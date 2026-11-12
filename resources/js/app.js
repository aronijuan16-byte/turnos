import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Fallback sin WebSockets: las páginas con <body data-autorefresh> se refrescan solas.
document.addEventListener('DOMContentLoaded', () => {
    if (window.realtime || !('autorefresh' in document.body.dataset)) return;
    setInterval(() => {
        const tag = document.activeElement?.tagName;
        if (document.hidden || ['INPUT', 'SELECT', 'TEXTAREA'].includes(tag)) return;
        window.location.reload();
    }, 5000);
});
