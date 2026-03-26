import './bootstrap';

// sync locale from localstorage or url
(function () {
    const config = window.__AGRI_CONFIG || {};
    const supportedLocales = config.supportedLocales || ['en', 'si', 'ta'];
    const currentLang = config.locale || 'en';

    const urlParams = new URLSearchParams(window.location.search);
    const urlLang = urlParams.get('lang');
    const storedLang = localStorage.getItem('agriassist_locale');

    if (urlLang && supportedLocales.includes(urlLang)) {
        localStorage.setItem('agriassist_locale', urlLang);
        return;
    }

    if (storedLang && storedLang !== currentLang) {
        if (window.location.pathname.includes('/lang/')) {
            localStorage.setItem('agriassist_locale', currentLang);
        } else {
            window.location.href = "/lang/" + storedLang;
        }
    } else if (!storedLang) {
        localStorage.setItem('agriassist_locale', currentLang);
    }
})();

window.themeApp = function () {
    return {
        darkMode: localStorage.getItem('agriassist_theme') === 'dark',
        toggleDark() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('agriassist_theme', this.darkMode ? 'dark' : 'light');
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
});
