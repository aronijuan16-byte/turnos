<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_finish_current_ticket_and_call_next_in_one_action(): void
    {
        $agent = User::factory()->create();
        $service = Service::create(['name' => 'Trámite', 'prefix' => 'A', 'active' => true]);
        $current = $this->createTicket($service, [
            'number' => 1,
            'code' => 'A001',
            'status' => 'serving',
            'window' => 'Ventanilla 2',
            'user_id' => $agent->id,
            'called_at' => now(),
        ]);
        $next = $this->createTicket($service, [
            'number' => 2,
            'code' => 'A002',
        ]);

        $response = $this->actingAs($agent)->post(route('agent.advance'));

        $response->assertRedirect(route('agent.panel'));
        $response->assertSessionHas('msg', 'Turno A001 atendido. Llamando a A002.');
        $this->assertDatabaseHas('tickets', [
            'id' => $current->id,
            'status' => 'done',
            'user_id' => $agent->id,
        ]);
        $this->assertNotNull($current->fresh()->finished_at);
        $this->assertDatabaseHas('tickets', [
            'id' => $next->id,
            'status' => 'calling',
            'window' => 'Ventanilla 2',
            'user_id' => $agent->id,
        ]);
    }

    public function test_agent_can_finish_last_ticket_without_calling_a_nonexistent_next_ticket(): void
    {
        $agent = User::factory()->create();
        $service = Service::create(['name' => 'Trámite', 'prefix' => 'A', 'active' => true]);
        $current = $this->createTicket($service, [
            'number' => 1,
            'code' => 'A001',
            'status' => 'calling',
            'window' => 'Ventanilla 1',
            'user_id' => $agent->id,
            'called_at' => now(),
        ]);

        $response = $this->actingAs($agent)->post(route('agent.advance'));

        $response->assertRedirect(route('agent.panel'));
        $response->assertSessionHas('msg', 'Turno A001 atendido. No hay más turnos en espera.');
        $this->assertDatabaseHas('tickets', ['id' => $current->id, 'status' => 'done']);
        $this->assertSame(0, Ticket::where('status', 'calling')->count());
    }

    public function test_public_display_shows_waiting_and_completed_ticket_codes_without_personal_details(): void
    {
        $service = Service::create(['name' => 'Trámite', 'prefix' => 'A', 'active' => true]);
        $this->createTicket($service, ['number' => 1, 'code' => 'A001']);
        $this->createTicket($service, [
            'number' => 2,
            'code' => 'A002',
            'status' => 'done',
            'finished_at' => now(),
        ]);

        $response = $this->get(route('display'));

        $response->assertOk();
        $response->assertSee('A001');
        $response->assertSee('A002');
        $response->assertDontSee('Ciudadano de prueba');
        $response->assertDontSee('00000000');
    }

    private function createTicket(Service $service, array $attributes): Ticket
    {
        return Ticket::create($attributes + [
            'service_id' => $service->id,
            'date' => today()->toDateString(),
            'citizen_name' => 'Ciudadano de prueba',
            'document' => '00000000',
        ]);
    }
}
