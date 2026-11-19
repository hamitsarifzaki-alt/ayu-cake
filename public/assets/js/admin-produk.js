(() => {
    'use strict';
    const initialize = () => {
    const lifecycle = typeof AbortController === 'function' ? new AbortController() : {signal: undefined, abort() {}};

    const input = document.getElementById('ad-product-photo');
    const preview = document.querySelector('[data-ad-photo-preview]');
    const empty = document.querySelector('[data-ad-photo-empty]');
    const clear = document.querySelector('[data-ad-photo-clear]');
    if (!input || !preview || !empty || !clear) return;
    const awal = preview.getAttribute('src');
    let url;
    const release = () => {
        if (url) URL.revokeObjectURL(url);
        url = null;
    };
    const show = src => {
        if (src) preview.setAttribute('src', src);
        else preview.removeAttribute('src');
        preview.hidden = !src;
        empty.hidden = Boolean(src);
    };
    input.addEventListener('change', () => {
        release();
        input.setCustomValidity('');
        const file = input.files?.[0];
        clear.hidden = !file;
        if (file && (file.size > 2 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
            input.setCustomValidity('Gunakan foto JPG, PNG, atau WebP maksimal 2 MB.');
            input.reportValidity();
            show(awal);
            return;
        }
        url = file ? URL.createObjectURL(file) : null;
        show(url || awal);
    });
    clear.addEventListener('click', () => {
        release();
        input.value = '';
        input.dispatchEvent(new Event('change', {bubbles: true}));
        input.setCustomValidity('');
        show(awal);
        clear.hidden = true;
        input.focus();
    });
    window.addEventListener('pagehide', release, {signal: lifecycle.signal});
    return () => { lifecycle.abort(); release(); };

    };
    if (typeof window !== 'undefined' && window.AyuUI) window.AyuUI.register('admin-produk', initialize);
    else initialize();
})();
