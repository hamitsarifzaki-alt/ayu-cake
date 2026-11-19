(() => {
    'use strict';
    const states = new WeakMap();
    const controls = form => [...form.elements].filter(input => input.name && /^(INPUT|SELECT|TEXTAREA)$/.test(input.tagName)
        && !['hidden', 'submit', 'button', 'reset'].includes(input.type) && !input.readOnly);
    const normalize = (value, input) => {
        const text = String(typeof value === 'boolean' ? Number(value) : value ?? '').replace(/\r\n/g, '\n');
        return input.type === 'password' || input.autocomplete.includes('password') ? text : text.trim();
    };
    const value = input => ['checkbox', 'radio'].includes(input.type) ? (input.checked ? input.value : '') : input.value;
    function setup(root = document) {
        root.querySelectorAll('form[data-track-changes]').forEach(form => {
            if (states.has(form)) return;
            const fields = controls(form);
            const baseline = form.dataset.formBaseline ? JSON.parse(form.dataset.formBaseline) : null;
            const original = new Map(fields.map(input => [input, normalize(baseline?.[input.name] ?? value(input), input)]));
            const saves = [...form.querySelectorAll('[data-save-changes]')];
            const cancels = [...form.querySelectorAll('[data-cancel-changes]')];
            const changed = () => fields.some(input => input.type === 'file' ? input.files.length > 0
                : normalize(value(input), input) !== original.get(input));
            const update = () => {
                const dirty = changed();
                saves.forEach(button => { button.disabled = !dirty || button.dataset.saveLocked === 'true'; });
                cancels.forEach(cancel => {
                    cancel.setAttribute('aria-disabled', String(!dirty));
                    if (cancel.tagName === 'BUTTON') cancel.disabled = !dirty;
                    else if (dirty) { cancel.href = cancel.dataset.cancelUrl; cancel.removeAttribute('tabindex'); }
                    else { cancel.removeAttribute('href'); cancel.tabIndex = -1; }
                });
            };
            states.set(form, {update, changed});
            update();
        });
    }
    const updateControl = event => {
        const form = event.target.form;
        if (form && states.has(form)) queueMicrotask(() => states.get(form).update());
    };
    document.addEventListener('input', updateControl, true);
    document.addEventListener('change', updateControl, true);
    document.addEventListener('reset', event => { if (states.has(event.target)) queueMicrotask(() => states.get(event.target).update()); }, true);
    document.addEventListener('submit', event => {
        const state = states.get(event.target);
        if (state && (!state.changed() || event.submitter?.dataset.saveLocked === 'true')) event.preventDefault();
    }, true);
    document.addEventListener('click', event => {
        const cancel = event.target.closest('[data-cancel-changes]');
        if (cancel?.getAttribute('aria-disabled') === 'true') event.preventDefault();
    }, true);
    setup();
    window.AyuFormChanges = {refresh: setup};
    document.addEventListener('ayu:content-updated', () => setup());
    window.addEventListener('pageshow', () => {
        setup();
        document.querySelectorAll('form[data-track-changes]').forEach(form => states.get(form)?.update());
    });
})();
