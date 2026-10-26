import './bootstrap';
import './tracking';
import './analytics';
import Alpine from 'alpinejs';

Alpine.directive('reveal', (el) => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const rect = el.getBoundingClientRect();
    const alreadyInView = rect.top < window.innerHeight && rect.bottom > 0;
    if (alreadyInView) {
        return;
    }

    el.classList.add('reveal-init');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                el.classList.add('reveal-visible');
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.15 });

    observer.observe(el);
});

window.Alpine = Alpine;
Alpine.start();
