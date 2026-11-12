<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Panel del agente</title> @vite(['resources/js/app.js'])
<style>body{font-family:system-ui;max-width:760px;margin:2rem auto;padding:0 1rem}button{padding:.6rem 1rem;margin:.2rem}
table{width:100%;border-collapse:collapse}td,th{border-bottom:1px solid #ddd;padding:.5rem;text-align:left}.box{background:#f3f6fb;padding:1rem;border-radius:8px;margin:1rem 0}</style></head><body data-autorefresh>
@include('partials.site-nav')
<h1>Panel de atención</h1>
@if(session('msg'))<p><b>{{ session('msg') }}</b></p>@endif
<div class="box">
@if($current)
 <h2>{{ $current->code }} — {{ $current->citizen_name }} <small>({{ $current->document }})</small></h2>
 <p>{{ $current->service->name }} · {{ $current->window }} · estado: {{ $current->status }}</p>
 @if($current->status==='calling')
 <form method="POST" action="{{ route('agent.transition',[$current,'serving']) }}" style="display:inline">@csrf
  <button type="submit">Iniciar atención</button>
 </form>
 <form method="POST" action="{{ route('agent.transition',[$current,'calling']) }}" style="display:inline">@csrf
  <button type="submit">Volver a llamar</button>
 </form>
 @endif
 <form method="POST" action="{{ route('agent.advance') }}">@csrf
  <button type="submit">Marcar atendido y llamar al siguiente</button>
 </form>
 <form method="POST" action="{{ route('agent.transition',[$current,'absent']) }}" style="display:inline">@csrf
  <button type="submit">Marcar ausente</button>
 </form>
@else
 <form method="POST" action="{{ route('agent.next') }}">@csrf
  <input name="window" value="{{ old('window', session('agent_window')) }}" placeholder="Ventanilla (ej. Ventanilla 2)" required>
  <button>Llamar siguiente</button></form>
@endif</div>
<h3>En espera (<span id="n">{{ $waiting->count() }}</span>)</h3>
<table><thead><tr><th>Turno</th><th>Servicio</th><th>Ciudadano</th></tr></thead><tbody id="w">
@foreach($waiting as $t)<tr data-id="{{ $t->id }}"><td>{{ $t->code }}</td><td>{{ $t->service->name }}</td><td>{{ $t->citizen_name }}</td></tr>@endforeach</tbody></table>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const w=document.getElementById('w'),n=document.getElementById('n');
 window.Echo.channel('queue').listen('.ticket.updated',({ticket:t})=>{
  const old=w.querySelector(`[data-id="${t.id}"]`);
  if(t.status==='waiting'&&!old){const r=w.insertRow();r.dataset.id=t.id;r.innerHTML=`<td>${t.code}</td><td>${t.service}</td><td>${t.name}</td>`;}
  else if(t.status!=='waiting'&&old)old.remove();
  n.textContent=w.rows.length;});});
</script></body></html>
