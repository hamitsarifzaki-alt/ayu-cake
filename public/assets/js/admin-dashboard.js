(() => {
    'use strict';

    window.addEventListener('pageshow', event => {
        if (event.persisted) window.location.reload();
    });
    document.querySelector('.ad-proof-error, .ad-form-error')?.focus();

    const menus = document.querySelectorAll('.ad-menu');
    function closeMenus(except) {
        menus.forEach(menu => {
            if (menu !== except) menu.open = false;
        });
    }
    menus.forEach(menu => menu.addEventListener('toggle', () => {
        if (menu.open) closeMenus(menu);
    }));
    document.addEventListener('click', event => {
        if (!event.target.closest('.ad-menu')) closeMenus();
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        const menu = document.querySelector('.ad-menu[open]');
        if (menu) {
            closeMenus();
            menu.querySelector('summary').focus();
        }
    });
})();
