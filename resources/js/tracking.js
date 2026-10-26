/**
 * Click tracking, first-party only:
 * - WhatsApp buttons (a[data-wa-track]) -> anonymous beacon to our own server
 *   (page, language, service/stone/project context). No cookies are set here.
 * - Every tracked click also emits a `jra:conversion` event that the
 *   consent-gated analytics loader (analytics.js) forwards -- nothing is sent
 *   to a third party without consent.
 */
const body = document.body;

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function conversion(name, detail = {}) {
    window.dispatchEvent(new CustomEvent('jra:conversion', { detail: { name, ...detail } }));
}

function whatsappBeacon() {
    const endpoint = body.dataset.waEndpoint;
    if (!endpoint) return;

    const data = new FormData();
    data.append('_token', csrf());
    data.append('page', window.location.pathname + window.location.search);
    data.append('locale', body.dataset.locale ?? '');
    if (body.dataset.waContextType && body.dataset.waContextId) {
        data.append('context_type', body.dataset.waContextType);
        data.append('context_id', body.dataset.waContextId);
    }

    if (!(navigator.sendBeacon && navigator.sendBeacon(endpoint, data))) {
        fetch(endpoint, { method: 'POST', body: data, keepalive: true, credentials: 'same-origin' }).catch(() => {});
    }
}

document.addEventListener('click', (event) => {
    const link = event.target.closest('a');
    if (!link) return;

    if (link.hasAttribute('data-wa-track')) {
        whatsappBeacon();
        conversion('whatsapp_click', { context: body.dataset.waContextType ?? null });
    } else if (link.protocol === 'tel:') {
        conversion('phone_click');
    }
});
