// Scroll-linked motion for the home page: each [x-cinema] element gets --p,
// from 0 (its top reaches the bottom of the viewport) to 1 (its bottom leaves
// the top). CSS turns --p into scale and drift. One passive scroll listener,
// work only for elements on screen, nothing at all for reduced motion.
const tracked = new Set();
const visible = new Set();
let frame = null;

const reduced = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function update() {
    frame = null;
    const vh = window.innerHeight;
    visible.forEach((el) => {
        const rect = el.getBoundingClientRect();
        // "exit" (the hero): 0 at the top of the page, 1 once scrolled past.
        const progress = el.dataset.cinema === 'exit'
            ? -rect.top / Math.max(rect.height, 1)
            : (vh - rect.top) / (vh + rect.height);
        el.style.setProperty('--p', Math.min(1, Math.max(0, progress)).toFixed(4));
    });
}

function schedule() {
    if (frame === null) {
        frame = window.requestAnimationFrame(update);
    }
}

let observer = null;

export function registerCinema(Alpine) {
    Alpine.directive('cinema', (el, _directive, { cleanup }) => {
        if (reduced() || !('IntersectionObserver' in window)) {
            return;
        }

        if (observer === null) {
            observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        visible.add(entry.target);
                        entry.target.classList.add('cinema-in');
                    } else {
                        visible.delete(entry.target);
                    }
                });
                schedule();
            }, { threshold: [0, 0.25] });
            window.addEventListener('scroll', schedule, { passive: true });
            window.addEventListener('resize', schedule, { passive: true });
        }

        el.classList.add('cinema-on');
        tracked.add(el);
        observer.observe(el);
        schedule();

        cleanup(() => {
            observer.unobserve(el);
            tracked.delete(el);
            visible.delete(el);
        });
    });
}
