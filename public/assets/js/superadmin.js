(() => {
    'use strict';
    const setupPasswordToggles = () => document.querySelectorAll('[data-sa-password-toggle]').forEach(button => { button.hidden = false; });
    setupPasswordToggles();
    document.addEventListener('ayu:content-updated', setupPasswordToggles);
    window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
    const error = document.querySelector('.sa-admin-error, .sa-customer-error, .sa-sales-error, .sa-store-error, .sa-faq-error');
    if (error) error.focus();
    else document.querySelector('[data-sa-form-focus]')?.focus({preventScroll: true});
    const menus = [...document.querySelectorAll('.ad-menu')];
    const closeMenus = except => menus.forEach(menu => { if (menu !== except) menu.open = false; });
    menus.forEach(menu => menu.addEventListener('toggle', () => { if (menu.open) closeMenus(menu); }));
    document.addEventListener('click', event => {
        const toggle = event.target.closest('[data-sa-password-toggle]');
        if (toggle) {
            const input = document.getElementById(toggle.getAttribute('aria-controls'));
            if (input) {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(show));
                toggle.setAttribute('aria-label', (show ? 'Sembunyikan ' : 'Tampilkan ') + toggle.dataset.passwordName);
                toggle.querySelector('[data-sa-eye-slash]').toggleAttribute('hidden', !show);
            }
        }
        if (!event.target.closest('.ad-menu')) closeMenus();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            const menu = menus.find(item => item.open);
            closeMenus();
            menu?.querySelector('summary').focus();
        }
    });
})();
