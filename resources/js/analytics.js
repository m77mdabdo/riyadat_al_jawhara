/**
 * Consent-gated analytics (GA4 and/or Plausible, configured server-side).
 * - Does nothing when <body data-analytics> is absent (no provider configured).
 * - Loads NOTHING third-party until the visitor accepts the consent banner.
 * - Forwards conversions: lead_submitted (thank-you page marker),
 *   whatsapp_click and phone_click (from tracking.js).
 */
const raw = document.body.dataset.analytics;
const config = raw ? JSON.parse(raw) : null;
let loaded = false;

function consent() {
    try {
        return JSON.parse(localStorage.getItem('jra_consent') || 'null')?.choice ?? null;
    } catch (e) {
        return null;
    }
}

function inject(src, attributes = {}) {
    const script = document.createElement('script');
    script.src = src;
    script.async = true;
    Object.entries(attributes).forEach(([key, value]) => script.setAttribute(key, value));
    document.head.appendChild(script);
}

function load() {
    if (loaded || !config) return;
    loaded = true;

    if (config.ga4) {
        window.dataLayer = window.dataLayer || [];
        window.gtag = function gtag() { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', config.ga4, { anonymize_ip: true });
        inject(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(config.ga4)}`);
    }

    if (config.plausible) {
        window.plausible = window.plausible || function plausible() { (window.plausible.q = window.plausible.q || []).push(arguments); };
        inject(config.plausible.src, { 'data-domain': config.plausible.domain, defer: '' });
    }

    const marker = document.querySelector('meta[name="jra-conversion"]');
    if (marker) send(marker.content);
}

function send(name, detail = {}) {
    if (!loaded) return;
    if (window.gtag) window.gtag('event', name, detail);
    if (window.plausible) window.plausible(name, { props: detail });
}

if (config) {
    if (consent() === 'granted') load();
    window.addEventListener('jra:consent', (event) => { if (event.detail?.choice === 'granted') load(); });
    window.addEventListener('jra:conversion', (event) => {
        const { name, ...detail } = event.detail || {};
        send(name, detail);
    });
}
