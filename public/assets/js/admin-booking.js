(() => {
    'use strict';
    const panel = document.getElementById('kalender-booking');
    const calendar = panel?.querySelector('.ad-booking-calendar');
    const settings = panel?.querySelector('.ad-booking-settings');
    if (!panel || !calendar || !settings) return;

    let pending = null;
    let saving = false;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const bookingUrl = value => {
        const url = new URL(value, window.location.href);
        return url.origin === window.location.origin && url.pathname === window.location.pathname
            && url.searchParams.get('page') === 'admin-booking' ? url : null;
    };
    const reveal = (element, horizontal = 0) => {
        if (reducedMotion.matches || !element.animate) return;
        element.getAnimations().forEach(animation => animation.cancel());
        element.animate([
            {opacity: 0, transform: horizontal ? `translateX(${horizontal}px)` : 'translateY(4px)'},
            {opacity: 1, transform: 'translate(0, 0)'}
        ], {duration: 160, easing: 'ease-out'});
    };
    const focus = element => {
        if (!element) return;
        if (!element.matches('a, button, input, select')) element.tabIndex = -1;
        element.focus({preventScroll: true});
    };
    const showConnectionError = (url, wasSaving) => {
        settings.querySelector('[data-booking-connection-error]')?.remove();
        const message = document.createElement('p');
        message.className = 'ad-booking-error';
        message.dataset.bookingConnectionError = '';
        message.setAttribute('role', 'alert');
        message.append(wasSaving
            ? 'Hasil penyimpanan belum dapat dipastikan. Periksa kembali pengaturan sebelum mencoba menyimpan lagi. '
            : 'Kalender belum dapat dimuat. Silakan coba kembali. ');
        const retry = document.createElement('a');
        retry.href = url.href;
        retry.textContent = wasSaving ? 'Periksa pengaturan' : 'Coba kembali';
        message.append(retry);
        settings.prepend(message);
        message.setAttribute('data-dismissible-notice', '');
        window.AyuNotice.refresh(settings);
        focus(message);
    };

    async function load(url, {body = null, historyMode = 'push', focusDate = '', focusLabel = ''} = {}) {
        if (saving) return;
        pending?.abort();
        const request = new AbortController();
        let timedOut = false;
        const timeout = window.setTimeout(() => {
            timedOut = true;
            request.abort();
        }, 25000);
        pending = request;
        saving = body !== null;
        const wasSaving = saving;
        const controls = wasSaving ? [...settings.querySelectorAll('input, select, button')] : [];
        const disabled = controls.map(control => control.disabled);
        controls.forEach(control => { control.disabled = true; });
        const submit = wasSaving ? settings.querySelector('button[type="submit"]') : null;
        const submitText = submit?.textContent;
        if (submit) submit.textContent = 'Menyimpan...';
        panel.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url.href, {
                method: wasSaving ? 'POST' : 'GET', body,
                credentials: 'same-origin', cache: 'no-store', signal: request.signal
            });
            const result = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (pending !== request) return;
            const next = result.getElementById('kalender-booking');
            const nextCalendar = next?.querySelector('.ad-booking-calendar');
            const nextSettings = next?.querySelector('.ad-booking-settings');
            if (!nextCalendar || !nextSettings) {
                const destination = new URL(response.url);
                if (response.redirected && destination.origin === window.location.origin
                    && destination.searchParams.get('page') === 'login') {
                    window.location.assign(destination.href);
                    return;
                }
                throw new Error('Kalender tidak tersedia');
            }

            const monthChanged = panel.dataset.bookingMonth !== next.dataset.bookingMonth;
            const direction = next.dataset.bookingMonth > panel.dataset.bookingMonth ? 8 : -8;
            if (monthChanged) {
                calendar.replaceChildren(...nextCalendar.childNodes);
                reveal(calendar, direction);
            } else {
                // Pertahankan tombol tanggal dan fokusnya; ubah hanya penanda serta statusnya.
                calendar.querySelectorAll('[data-booking-date]').forEach(day => {
                    const updated = nextCalendar.querySelector(`[data-booking-date="${day.dataset.bookingDate}"]`);
                    if (!updated) return;
                    day.className = updated.className;
                    day.setAttribute('aria-label', updated.getAttribute('aria-label'));
                    if (updated.hasAttribute('aria-current')) day.setAttribute('aria-current', 'true');
                    else day.removeAttribute('aria-current');
                    const label = day.querySelector('small');
                    const newLabel = updated.querySelector('small');
                    if (!newLabel) label?.remove();
                    else if (label) label.textContent = newLabel.textContent;
                    else day.append(newLabel);
                });
            }
            settings.replaceChildren(...nextSettings.childNodes);
            window.AyuNotice.refresh(settings);
            window.AyuFormChanges.refresh(settings);
            panel.dataset.bookingMonth = next.dataset.bookingMonth;
            reveal(settings);

            const destination = bookingUrl(response.url) || url;
            destination.hash = 'kalender-booking';
            if (historyMode !== 'none') {
                window.history[wasSaving || historyMode === 'replace' ? 'replaceState' : 'pushState'](
                    window.history.state, '', destination.href
                );
            }
            const error = settings.querySelector('.ad-booking-error');
            if (error) focus(error);
            else if (wasSaving) focus(settings.querySelector('#ad-booking-selected-title'));
            else if (focusDate) focus(calendar.querySelector(`[data-booking-date="${focusDate}"]`));
            else if (focusLabel) focus([...calendar.querySelectorAll('a[aria-label]')]
                .find(link => link.getAttribute('aria-label') === focusLabel));
        } catch (error) {
            if ((error.name !== 'AbortError' || timedOut) && pending === request) showConnectionError(url, wasSaving);
        } finally {
            window.clearTimeout(timeout);
            if (pending === request) {
                pending = null;
                saving = false;
                panel.removeAttribute('aria-busy');
                controls.forEach((control, index) => {
                    if (control.isConnected) control.disabled = disabled[index];
                });
                if (submit?.isConnected) submit.textContent = submitText;
            }
        }
    }

    panel.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || link.target === '_blank'
            || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = bookingUrl(link.href);
        if (!url) return;
        event.preventDefault();
        if (!saving) load(url, {
            focusDate: link.dataset.bookingDate || '',
            focusLabel: link.getAttribute('aria-label') || ''
        });
    });
    panel.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        const form = event.target;
        if (!form.matches('.ad-booking-form')) return;
        const url = bookingUrl(form.action);
        if (!url) return;
        event.preventDefault();
        if (!saving) load(url, {body: new FormData(form)});
    });
    window.addEventListener('popstate', () => {
        const url = bookingUrl(window.location.href);
        if (url && !saving) load(url, {historyMode: 'none'});
    });
})();
