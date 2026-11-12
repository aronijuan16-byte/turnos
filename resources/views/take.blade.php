<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Solicitar turno</title>
<style>body{font-family:system-ui;max-width:420px;margin:2rem auto;padding:0 1rem}label{display:block;margin:.8rem 0 .2rem}
input,select,button{width:100%;padding:.7rem;font-size:1rem;box-sizing:border-box}button{margin-top:1rem;background:#0a58ca;color:#fff;border:0;border-radius:6px}.e{color:#b00}</style></head><body>
@include('partials.site-nav')
<h1>Solicita tu turno</h1>
@foreach($errors->all() as $e)<p class="e">{{ $e }}</p>@endforeach
<form method="POST" action="{{ route('ticket.store') }}">@csrf
<label>Servicio</label><select name="service_id" required>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
<label>Nombres y apellidos</label><input name="citizen_name" value="{{ old('citizen_name') }}" required>
<label>DNI</label><input name="document" value="{{ old('document') }}" inputmode="numeric" required>
<button>Obtener turno</button></form></body></html>
