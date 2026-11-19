(() => {
    'use strict';
    const initialize = () => {
    const lifecycle = typeof AbortController === 'function' ? new AbortController() : {signal: undefined, abort() {}};

    const form = document.querySelector('[data-order-shipping-form]');
    if (form) {
        const submit = form.querySelector('[data-order-shipping-submit]');
        const ongkir = form.elements.ongkir;
        const preview = form.querySelector('[data-order-total-preview]');
        const tolak = form.querySelector('[data-order-reject-toggle]');
        const alasan = document.getElementById(tolak.getAttribute('aria-controls'));
        tolak.addEventListener('click', () => {
            const buka = alasan.hidden;
            alasan.hidden = !buka;
            tolak.setAttribute('aria-expanded', String(buka));
            if (buka) alasan.querySelector('[name="catatan_status"]').focus();
        }, {signal: lifecycle.signal});
        const subtotal = Number(form.dataset.subtotal);
        const rupiah = new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', maximumFractionDigits: 0});
        const tampilkanTotal = () => {
            preview.textContent = ongkir.validity.valid ? rupiah.format(subtotal + Number(ongkir.value)) : 'Isi ongkir';
        };
        ongkir.addEventListener('input', tampilkanTotal, {signal: lifecycle.signal});
        tampilkanTotal();
        form.addEventListener('submit', event => {
            if (form.dataset.sending === '1') { event.preventDefault(); return; }
            if (!form.reportValidity()) { event.preventDefault(); return; }
            form.dataset.sending = '1';
            submit.disabled = true;
            submit.textContent = 'Mengirim konfirmasi...';
        });
        window.addEventListener('pageshow', () => {
            delete form.dataset.sending;
            submit.disabled = false;
            submit.textContent = 'Konfirmasi & Kirim Ongkir';
            tampilkanTotal();
        }, {signal: lifecycle.signal});
    }
    const status = document.getElementById('ad-order-status');
    const statusSubmit = document.querySelector('[data-order-status-submit]');
    if (status && statusSubmit) {
        const update = () => { statusSubmit.disabled = status.value !== 'Selesai'; };
        status.addEventListener('change', update);
        window.addEventListener('pageshow', update, {signal: lifecycle.signal});
        update();
    }
    return () => lifecycle.abort();

    };
    if (typeof window !== 'undefined' && window.AyuUI) window.AyuUI.register('admin-detail-pesanan', initialize);
    else initialize();
})();
