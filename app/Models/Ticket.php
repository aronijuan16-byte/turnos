<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['service_id','date','number','code','citizen_name','document',
        'status','window','user_id','called_at','finished_at'];
    protected $casts = ['date' => 'date', 'called_at' => 'datetime', 'finished_at' => 'datetime'];

    public function service() { return $this->belongsTo(Service::class); }

    /** Turnos en espera delante de este (en todo el local, orden de llegada). */
    public function position(): int
    {
        return static::whereDate('date', $this->date)->where('status', 'waiting')->where('id', '<', $this->id)->count();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->id, 'code' => $this->code, 'status' => $this->status,
            'window' => $this->window, 'service' => $this->service->name,
            'name' => $this->citizen_name,
        ];
    }
}
