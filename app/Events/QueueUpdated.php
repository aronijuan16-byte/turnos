<?php
namespace App\Events;
use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $ticket;
    public function __construct(Ticket $ticket) { $this->ticket = $ticket->load('service')->toPayload(); }

    public function broadcastOn(): array { return [new Channel('queue')]; }
    public function broadcastAs(): string { return 'ticket.updated'; }
}
