<nav style="display:flex;flex-wrap:wrap;gap:.5rem 1rem;align-items:center;padding:.6rem 1rem;background:#0a58ca;font-family:system-ui;font-size:.95rem;border-radius:8px;margin-bottom:1rem">
    @php
        $links = [
            ['take', 'Solicitar turno', 'take'],
            ['display', 'Pantalla pública', 'display'],
            ['agent.panel', 'Panel del agente', 'agent.*'],
        ];
    @endphp
    @foreach($links as [$route, $label, $pattern])
        <a href="{{ route($route) }}" style="color:#fff;text-decoration:none;{{ request()->routeIs($pattern) ? 'font-weight:700;text-decoration:underline' : '' }}">{{ $label }}</a>
    @endforeach
    <span style="flex:1"></span>
    @auth
        <span style="color:#dbe7ff">{{ auth()->user()->name }}</span>
        <a href="{{ route('profile.edit') }}" style="color:#fff;text-decoration:none">Perfil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf
            <button type="submit" style="width:auto;margin:0;padding:.3rem .7rem;background:#fff;color:#0a58ca;border:0;border-radius:6px;cursor:pointer">Salir</button>
        </form>
    @else
        <a href="{{ route('login') }}" style="color:#fff;text-decoration:none">Ingresar (agentes)</a>
    @endauth
</nav>
