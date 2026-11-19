(() => {
    'use strict';
    let selectedWallet;
    const initialize = () => {
    const previews = new Set();
    const form = document.getElementById('sa-store-form');
    if (!form) return;
    const initial = JSON.parse(document.getElementById('sa-store-initial').textContent);
    const save = form.querySelector('[data-store-save]');
    const cancel = form.querySelector('[data-store-cancel]');
    const initialValue = control => control.name.replaceAll(']', '').split('[').reduce((value, key) => value?.[key], initial);
    const picker = form.querySelector('[data-store-wallet-select]');
    const showWallet = code => {
        picker.value = code;
        selectedWallet = picker.value;
        form.querySelectorAll('[data-store-wallet]').forEach(wallet => {
            wallet.hidden = wallet.dataset.storeWallet !== selectedWallet;
        });
    };
    showWallet(selectedWallet ?? picker.value);
    picker.addEventListener('change', () => showWallet(picker.value));
    const normalize = value => String(value ?? '').replace(/\r\n/g, '\n').trim();
    const dirty = () => [...form.elements].some(control => {
        if (!control.name || control.type === 'hidden' || control.type === 'submit') return false;
        if (control.type === 'file') return control.files.length > 0;
        const original = initialValue(control);
        return normalize(control.value) !== normalize(original);
    });
    const update = () => {
        form.querySelectorAll('[data-store-wallet]').forEach(wallet => {
            const number = wallet.querySelector('[data-wallet-number]');
            const owner = wallet.querySelector('[data-wallet-owner]');
            const used = Boolean(number.value.trim() || owner.value.trim());
            number.required = owner.required = used;
        });
        save.disabled = form.dataset.storeLocked === 'true' || !dirty();
        cancel.disabled = save.disabled;
    };
    cancel.addEventListener('click', () => {
        if (cancel.disabled) return;
        for (const control of form.elements) {
            if (!control.name || ['hidden', 'submit', 'button'].includes(control.type)) continue;
            if (control.type === 'file') {
                control.value = '';
                control.dispatchEvent(new Event('change', {bubbles: true}));
            } else control.value = initialValue(control) ?? '';
        }
        form.querySelectorAll('.sa-store-error').forEach(error => error.remove());
        update();
    });
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    form.noValidate = true;
    form.addEventListener('submit', event => {
        if (!dirty() || form.dataset.storeLocked === 'true') { event.preventDefault(); return; }
        const invalid = [...form.elements].find(control => control.willValidate && !control.validity.valid);
        if (invalid) {
            event.preventDefault();
            const wallet = invalid.closest('[data-store-wallet]');
            if (wallet) showWallet(wallet.dataset.storeWallet);
            invalid.reportValidity();
        }
    });
    update();
    if (form.querySelector('.sa-store-error')) {
        const invalidWallet = [...form.querySelectorAll('[data-store-wallet]')]
            .find(wallet => [...wallet.querySelectorAll('input')].some(input => !input.validity.valid));
        if (invalidWallet) showWallet(invalidWallet.dataset.storeWallet);
    }

    document.querySelectorAll('[data-store-upload]').forEach(input => {
        const image = document.getElementById(input.dataset.storeUpload);
        const status = document.querySelector(`[data-store-status="${input.dataset.storeUpload}"]`);
        const empty = document.querySelector(`[data-store-empty="${input.dataset.storeUpload}"]`);
        let objectUrl;
        const originalSrc = image.getAttribute('src');
        const originalHidden = image.hidden;
        const restore = () => { if (originalSrc) image.setAttribute('src', originalSrc); else image.removeAttribute('src'); image.hidden = originalHidden; if (empty) empty.hidden = !originalHidden; };
        input.addEventListener('change', () => {
            if (objectUrl) { URL.revokeObjectURL(objectUrl); previews.delete(objectUrl); objectUrl = null; }
            const file = input.files[0];
            if (!file) { restore(); status.textContent = ''; update(); return; }
            if (!['image/png', 'image/jpeg'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                input.value = ''; restore(); status.textContent = 'Pilih PNG atau JPG maksimal 2 MB.'; update(); return;
            }
            objectUrl = URL.createObjectURL(file);
            previews.add(objectUrl);
            image.src = objectUrl; image.hidden = false; if (empty) empty.hidden = true;
            status.textContent = `${file.name} — belum disimpan.`;
            update();
        });
    });
    return () => previews.forEach(url => URL.revokeObjectURL(url));

    };
    if (typeof window !== 'undefined' && window.AyuUI) window.AyuUI.register('superadmin-profil-toko', initialize);
    else initialize();
})();
