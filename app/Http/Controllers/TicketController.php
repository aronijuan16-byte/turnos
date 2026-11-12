<?php
namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    // --- Ciudadano ---
    public function create()
    {
        return view('take', ['services' => Service::where('active', true)->get()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'service_id' => 'required|exists:services,id',
            'citizen_name' => 'required|string|max:100',
            'document' => 'required|string|max:20',
        ]);

        $ticket = DB::transaction(function () use ($data) {
            $today = today()->toDateString();
            $service = Service::lockForUpdate()->findOrFail($data['service_id']);
            $next = (int) Ticket::where('service_id', $service->id)->where('date', $today)->max('number') + 1;
            return Ticket::create($data + [
                'date' => $today, 'number' => $next,
                'code' => $service->prefix . str_pad($next, 3, '0', STR_PAD_LEFT),
            ]);
        });

        broadcast(new QueueUpdated($ticket));
        return redirect()->route('ticket.show', $ticket);
    }

    public function show(Ticket $ticket)
    {
        return view('ticket', ['ticket' => $ticket->load('service'), 'ahead' => $ticket->position()]);
    }

    // --- Pantalla pública ---
    public function display()
    {
        return view('display', [
            'calling' => Ticket::with('service')->whereDate('date', today())
                ->whereIn('status', ['calling', 'serving'])->latest('called_at')->take(6)->get(),
            'waiting' => Ticket::with('service')->whereDate('date', today())
                ->where('status', 'waiting')->orderBy('id')->get(),
            'completed' => Ticket::whereDate('date', today())->where('status', 'done')
                ->latest('finished_at')->get(),
        ]);
    }

    // --- Agente (requiere auth) ---
    public function panel()
    {
        $waiting = Ticket::with('service')->whereDate('date', today())->where('status', 'waiting')->orderBy('id')->get();
        $current = Ticket::with('service')->whereDate('date', today())->where('user_id', auth()->id())
            ->whereIn('status', ['calling', 'serving'])->first();
        return view('agent.panel', compact('waiting', 'current'));
    }

    public function callNext(Request $r)
    {
        $r->validate(['window' => 'required|string|max:30']);
        $ticket = DB::transaction(function () use ($r) {
            $t = Ticket::whereDate('date', today())->where('status', 'waiting')
                ->orderBy('id')->lockForUpdate()->first();
            if ($t) $t->update(['status' => 'calling', 'window' => $r->window,
                'user_id' => auth()->id(), 'called_at' => now()]);
            return $t;
        });
        session(['agent_window' => $r->input('window')]);
        if ($ticket) broadcast(new QueueUpdated($ticket));
        return back()->with('msg', $ticket ? "Llamando a {$ticket->code}" : 'No hay turnos en espera');
    }

    public function completeAndCallNext()
    {
        $userId = auth()->id();
        [$completed, $next] = DB::transaction(function () use ($userId) {
            $current = Ticket::whereDate('date', today())->where('user_id', $userId)
                ->whereIn('status', ['calling', 'serving'])->lockForUpdate()->first();

            if (!$current) {
                return [null, null];
            }

            $current->update(['status' => 'done', 'finished_at' => now()]);

            $next = Ticket::whereDate('date', today())->where('status', 'waiting')
                ->orderBy('id')->lockForUpdate()->first();

            if ($next) {
                $next->update([
                    'status' => 'calling',
                    'window' => $current->window,
                    'user_id' => $userId,
                    'called_at' => now(),
                ]);
            }

            return [$current, $next];
        });

        if (!$completed) {
            return redirect()->route('agent.panel')->with('msg', 'No tiene un turno activo para atender.');
        }

        broadcast(new QueueUpdated($completed));
        if ($next) {
            broadcast(new QueueUpdated($next));
            return redirect()->route('agent.panel')->with('msg', "Turno {$completed->code} atendido. Llamando a {$next->code}.");
        }

        return redirect()->route('agent.panel')->with('msg', "Turno {$completed->code} atendido. No hay más turnos en espera.");
    }

    public function transition(Request $r, Ticket $ticket, string $to)
    {
        abort_unless(in_array($to, ['calling', 'serving', 'done', 'absent']) && $ticket->user_id === auth()->id(), 403);
        $ticket->update([
            'status' => $to,
            'called_at' => $to === 'calling' ? now() : $ticket->called_at, // re-llamar
            'finished_at' => in_array($to, ['done', 'absent']) ? now() : null,
        ]);
        broadcast(new QueueUpdated($ticket));
        return back();
    }
}
