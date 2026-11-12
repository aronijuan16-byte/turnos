<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Turno {{ $ticket->code }}</title> @vite(['resources/js/app.js'])
<style>body{font-family:system-ui;text-align:center;margin:2rem auto;max-width:420px}.code{font-size:4rem;font-weight:700}
#st{padding:1rem;border-radius:8px;background:#eef}.calling{background:#ffe08a!important}</style></head><body data-autorefresh>
@include('partials.site-nav')
<p>{{ $ticket->service->name }}</p><div class="code">{{ $ticket->code }}</div>
<div id="st">@if($ticket->status==='waiting')Turnos antes que usted: <b id="ahead">{{ $ahead }}</b>@elseif(in_array($ticket->status,['calling','serving'])){{ $ticket->status==='calling' ? '¡Es su turno! Diríjase a ' : 'En atención en ' }}{{ $ticket->window }}@elseif($ticket->status==='done')Atención finalizada @else Turno cancelado por ausencia @endif</div>
<script>
const id={{ $ticket->id }}, el=document.getElementById('st');
const labels={calling:'¡Es su turno! Diríjase a ',serving:'En atención en ',done:'Atención finalizada',absent:'Turno cancelado por ausencia'};
document.addEventListener('DOMContentLoaded',()=>{
 window.Echo.channel('queue').listen('.ticket.updated',e=>{
  const t=e.ticket;
  if(t.id===id){el.className=t.status==='calling'?'calling':'';
   el.textContent=(labels[t.status]||'')+((t.status==='calling'||t.status==='serving')?t.window:'');
   if(t.status==='calling'&&navigator.vibrate)navigator.vibrate([300,100,300]);}
  else if(t.status!=='waiting'&&t.id<id){const a=document.getElementById('ahead');if(a&&+a.textContent>0&&t.status==='calling')a.textContent=+a.textContent-1;}
 });});
</script>
<p style="margin-top:2rem"><a href="{{ route('take') }}">Solicitar otro turno</a> · <a href="{{ route('display') }}">Ver pantalla</a></p></body></html>
