<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TicketController::class, 'create'])->name('take');
Route::post('/turnos', [TicketController::class, 'store'])->middleware('throttle:10,1')->name('ticket.store');
Route::get('/turnos/{ticket}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/pantalla', [TicketController::class, 'display'])->name('display');

Route::middleware('auth')->prefix('agente')->group(function () {
    Route::get('/', [TicketController::class, 'panel'])->name('agent.panel');
    Route::post('/siguiente', [TicketController::class, 'callNext'])->name('agent.next');
    Route::post('/atender-siguiente', [TicketController::class, 'completeAndCallNext'])->name('agent.advance');
    Route::post('/turnos/{ticket}/{to}', [TicketController::class, 'transition'])->name('agent.transition');
});

// Breeze redirige a "dashboard" tras login/registro: lo enlazamos con el panel del agente.
Route::get('/dashboard', fn () => redirect()->route('agent.panel'))->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
