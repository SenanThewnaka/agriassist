import './bootstrap';

// locale sync setup
(function () {
    const config = window.__AGRI_CONFIG || {};
    const currentLang = config.locale || 'en';

    // Store the current server-side locale in localStorage to keep it in sync
    localStorage.setItem('agriassist_locale', currentLang);
})();

window.themeApp = function () {
    return {
        darkMode: localStorage.getItem('agriassist_theme') === 'dark',
        mobileMenuOpen: false,
        toggleDark() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('agriassist_theme', this.darkMode ? 'dark' : 'light');
            window.dispatchEvent(new CustomEvent('agriassist-theme-changed', { detail: { darkMode: this.darkMode } }));
        },
        toggleMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        }
    };
};

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // scroll reveal cleanup
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                revealObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    window.revealObserver = revealObserver;

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    // Sticky nav shadow
    window.addEventListener('scroll', () => {
        const navContainer = document.getElementById('nav-container');
        if (!navContainer) return;

        if (window.scrollY > 20) {
            navContainer.classList.add('shadow-md');
            navContainer.classList.remove('shadow-sm');
        } else {
            navContainer.classList.remove('shadow-md');
            navContainer.classList.add('shadow-sm');
        }
    });

    // Real-Time Notifications Listener
    if (window.Echo && window.__AGRI_CONFIG?.user_id) {
        window.Echo.private(`App.Models.User.${window.__AGRI_CONFIG.user_id}`)
            .listen('.order.placed', (e) => {
                if (window.showToast) {
                    window.showToast(`${e.message} from ${e.buyer_name} (Rs. ${e.total_price})`, 'success');
                } else {
                    alert(`${e.message} from ${e.buyer_name}`);
                }

                // Dispatch global event for page-specific updates
                window.dispatchEvent(new CustomEvent('order-placed', { detail: e }));
            });

        window.Echo.private(`App.Models.User.${window.__AGRI_CONFIG.user_id}`)
            .listen('.order.status.updated', (e) => {
                if (window.showToast) {
                    window.showToast(e.message, 'info');
                }

                // Dispatch global event for page-specific updates
                window.dispatchEvent(new CustomEvent('order-status-updated', { detail: e }));
            });
    }
});
