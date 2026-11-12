<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Pantalla de turnos</title> @vite(['resources/js/app.js'])
<style>body{font-family:system-ui;background:#0b1f3a;color:#fff;margin:0;padding:2rem}h1{text-align:center}
.controls{display:flex;justify-content:center;align-items:center;gap:1rem;margin:1.5rem 0}.controls button{font:inherit;padding:.7rem 1rem;border:0;border-radius:8px;cursor:pointer}
.voice-status{color:#d5dfef}
.boards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.board{min-width:0;padding:1rem;background:#10294b;border-radius:10px}.board h2{font-size:1.4rem;text-align:center}.board-list{max-height:70vh;overflow-y:auto}.row{display:flex;justify-content:space-between;gap:.5rem;font-size:2rem;padding:.8rem;margin:.6rem 0;background:#16325c;border-radius:10px}.row small{font-size:1rem;align-self:center}
.row.new{animation:f 1s 4;background:#c99700}@keyframes f{50%{opacity:.3}}</style></head><body data-autorefresh>
<div style="max-width:520px;margin:0 auto">@include('partials.site-nav')</div>
<h1>Estado de la cola</h1>
<div class="controls">
 <button id="voice-toggle" type="button">Activar anuncios por voz</button>
 <span id="voice-status" class="voice-status" role="status">Voz desactivada</span>
</div>
<div class="boards">
 <section class="board"><h2>En atención</h2><div id="list" class="board-list">
 @foreach($calling as $t)<div class="row" data-id="{{ $t->id }}"><b>{{ $t->code }}</b><small>{{ $t->window }}</small></div>@endforeach</div></section>
 <section class="board"><h2>En espera (<span id="waiting-count">{{ $waiting->count() }}</span>)</h2><div id="waiting-list" class="board-list">
 @foreach($waiting as $t)<div class="row" data-id="{{ $t->id }}"><b>{{ $t->code }}</b><small>{{ $t->service->name }}</small></div>@endforeach</div></section>
 <section class="board"><h2>Atendidos hoy</h2><div id="completed-list" class="board-list">
 @foreach($completed as $t)<div class="row" data-id="{{ $t->id }}"><b>{{ $t->code }}</b></div>@endforeach</div></section>
</div>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const voiceToggle=document.getElementById('voice-toggle');
 const voiceStatus=document.getElementById('voice-status');
 const activeList=document.getElementById('list');
 const waitingList=document.getElementById('waiting-list');
 const completedList=document.getElementById('completed-list');
 const waitingCount=document.getElementById('waiting-count');
 let voiceEnabled=false;
 const digitNames=['cero','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve'];
 const removeTicket=(list,id)=>list.querySelector(`[data-id="${id}"]`)?.remove();
 const makeRow=(ticket,includeService=false)=>{
  const row=document.createElement('div'),code=document.createElement('b');
  row.className='row';row.dataset.id=ticket.id;code.textContent=ticket.code;row.append(code);
  if(includeService){const service=document.createElement('small');service.textContent=ticket.service;row.append(service);}
  return row;
 };
 const updateWaitingCount=()=>{waitingCount.textContent=waitingList.children.length;};
 const speak=(text)=>{
  if(!voiceEnabled||!('speechSynthesis' in window))return;
  speechSynthesis.cancel();
  const utterance=new SpeechSynthesisUtterance(text);
  utterance.lang='es-PE';
  utterance.rate=0.9;
  const spanishVoice=speechSynthesis.getVoices().find(voice=>voice.lang.toLowerCase().startsWith('es'));
  if(spanishVoice)utterance.voice=spanishVoice;
  speechSynthesis.speak(utterance);
 };
 if(!('speechSynthesis' in window)){
  voiceToggle.disabled=true;
  voiceStatus.textContent='Este navegador no admite anuncios por voz';
 }else{
  voiceToggle.addEventListener('click',()=>{
   voiceEnabled=!voiceEnabled;
   voiceToggle.textContent=voiceEnabled?'Desactivar anuncios por voz':'Activar anuncios por voz';
   voiceStatus.textContent=voiceEnabled?'Voz activada':'Voz desactivada';
   if(voiceEnabled)speak('Prueba de voz. Los anuncios de turnos están activados.');
   else speechSynthesis.cancel();
  });
 }
 window.Echo.channel('queue').listen('.ticket.updated',({ticket:t})=>{
  removeTicket(activeList,t.id);
  removeTicket(waitingList,t.id);
  removeTicket(completedList,t.id);
  if(t.status==='calling'||t.status==='serving'){
   const row=makeRow(t);row.classList.toggle('new',t.status==='calling');
   const windowName=document.createElement('small');windowName.textContent=t.window;row.append(windowName);activeList.prepend(row);
   if(t.status==='calling'){
    const spokenCode=Array.from(t.code.toUpperCase(),character=>/\d/.test(character)?digitNames[Number(character)]:character).join(', ');
    speak(`Atención. Turno ${spokenCode}. Por favor, acérquese a ${t.window}.`);
   }
   while(activeList.children.length>6)activeList.lastChild.remove();
  }else if(t.status==='waiting'){
   waitingList.append(makeRow(t,true));
  }else if(t.status==='done'){
   completedList.prepend(makeRow(t));
  }
  updateWaitingCount();
 });});
</script></body></html>
