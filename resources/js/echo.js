import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const key = import.meta.env.VITE_REVERB_APP_KEY;

if (key) {
    window.realtime = true;
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} else {
    // Sin Reverb configurado: Echo no-op para que las pantallas no fallen,
    // y se recargan solas cada pocos segundos (ver app.js).
    window.realtime = false;
    const channel = { listen() { return channel; } };
    window.Echo = { channel: () => channel };
}
