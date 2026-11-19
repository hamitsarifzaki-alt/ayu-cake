(() => {
    'use strict';
    const initialize = () => {


    const forms = document.querySelectorAll('[data-po-cancel]');
    if (!forms.length) return;
    const confirmed = new WeakSet();
    let activeForm = null;
    const dialog = document.createElement('dialog');
    dialog.className = 'po-cancel-dialog';
    dialog.setAttribute('aria-labelledby', 'po-cancel-title');
    dialog.setAttribute('aria-describedby', 'po-cancel-description');
    dialog.innerHTML = '<h2 id="po-cancel-title">Batalkan pesanan ini?</h2>'
        + '<p id="po-cancel-description">Pesanan yang dibatalkan tidak dapat dilanjutkan.</p>'
        + '<div class="po-actions"><button type="button" class="po-button po-secondary" data-cancel-back autofocus>Kembali</button>'
        + '<button type="button" class="po-button po-danger" data-cancel-confirm>Ya, Batalkan</button></div>';
    document.body.appendChild(dialog);
    dialog.querySelector('[data-cancel-back]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-cancel-confirm]').addEventListener('click', () => {
        const form = activeForm;
        dialog.close();
        if (!form) return;
        confirmed.add(form);
        form.requestSubmit();
    });
    dialog.addEventListener('close', () => { activeForm = null; });
    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!confirmed.has(form)) {
                event.preventDefault();
                activeForm = form;
                dialog.showModal();
                return;
            }
            const tombol = form.querySelector('button[type="submit"]');

            if (tombol) {
                tombol.disabled = true;
                tombol.textContent = 'Membatalkan...';
            }
        });
    });
    return () => dialog.remove();

    };
    if (typeof window !== 'undefined' && window.AyuUI) window.AyuUI.register('pesanan', initialize);
    else initialize();
})();
