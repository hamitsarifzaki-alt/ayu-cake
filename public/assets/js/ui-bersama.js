(() => {
    'use strict';
    function statusFor(control) {
        if (control.form?.id === 'login-form' || control.form?.method !== 'post'
            || control.readOnly || control.matches(':disabled')
            || ['radio', 'checkbox', 'search'].includes(control.type)) return '';
        if (control.required) return '*';
        return control.hasAttribute('data-optional') ? '(opsional)' : '';
    }
    function mark(container, control) {
        const text = statusFor(control);
        let badge = container.querySelector(':scope > .field-status');
        const plain = [...container.childNodes].filter(node => node !== badge && (node.nodeType === Node.TEXT_NODE || node.nodeType === Node.ELEMENT_NODE))
            .map(node => node.nodeType === Node.ELEMENT_NODE && /^(INPUT|SELECT|TEXTAREA|BUTTON)$/.test(node.tagName) ? '' : node.textContent).join(' ');
        if (!text || /\b(?:wajib|opsional)\b|\*/i.test(plain)) {
            if (badge) badge.remove(); return;
        }
        if (!badge) {
            badge = document.createElement('small'); badge.className = 'field-status';
            const wrappedControl = [...container.children].find(el => el === control || el.contains(control));
            container.insertBefore(badge, wrappedControl || null);
        }
        badge.textContent = text;
        badge.classList.toggle('field-status-required', text === '*');
        if (text === '*') badge.setAttribute('aria-hidden', 'true');
        else badge.removeAttribute('aria-hidden');
    }
    function updateForms() {
        for (const form of document.forms) {
            const controls = [...form.elements].filter(el => /^(INPUT|SELECT|TEXTAREA)$/.test(el.tagName) && !['hidden','submit','button','reset'].includes(el.type));
            if (!controls.length) continue;
            for (const control of controls) {
                const labels = [...(control.labels || [])];
                labels.forEach(label => mark(label, control));
            }
        }
    }
    updateForms();
    document.addEventListener('change', updateForms);
    new MutationObserver(updateForms).observe(document.body, {subtree:true, attributes:true, attributeFilter:['required','disabled','readonly']});
    const initializedCompleteForms = new WeakSet();
    function setupCompletionForms() { document.querySelectorAll('form.customer-complete-form').forEach(form => {
        if (initializedCompleteForms.has(form)) return;
        initializedCompleteForms.add(form);
        form.addEventListener('submit', event => {
            if (form.dataset.sending) { event.preventDefault(); return; }
            form.dataset.sending = '1';
            const button = form.querySelector('button[type=submit]'); button.disabled = true; button.textContent = 'Menyimpan...';
        });
    }); }
    setupCompletionForms();
    const initializedNotices = new WeakMap();
    function prepareNotice(notice) {
        if (initializedNotices.has(notice)) return initializedNotices.get(notice);
        notice.classList.add('ui-notice');
        let close = notice.querySelector('[data-dismiss-notice]');
        if (!close) {
            close = document.createElement('button');
            close.type = 'button';
            close.dataset.dismissNotice = '';
            close.setAttribute('aria-label', 'Tutup pemberitahuan');
            close.textContent = '×';
            notice.append(close);
        }
        let timer;
        const pause = () => clearTimeout(timer);
        const dismiss = () => {
            pause();
            const focused = notice.contains(document.activeElement);
            const main = notice.closest('main') || document.querySelector('main');
            const target = notice.nextElementSibling?.querySelector('h2, h1')
                || main?.querySelector('h1, h2');
            if (notice.hasAttribute('data-notice-reusable')) notice.hidden = true;
            else notice.remove();
            if (focused && target) {
                target.tabIndex = -1;
                target.focus({preventScroll: true});
            }
        };
        const resume = () => {
            pause();
            if (notice.hidden || !notice.isConnected || notice.matches(':hover')
                || document.activeElement !== notice && notice.contains(document.activeElement)) return;
            timer = setTimeout(dismiss, 5000);
        };
        close.addEventListener('click', dismiss);
        notice.addEventListener('mouseenter', pause);
        notice.addEventListener('mouseleave', resume);
        notice.addEventListener('focusin', event => { if (event.target !== notice) pause(); });
        notice.addEventListener('focusout', event => {
            if (!notice.contains(event.relatedTarget)) setTimeout(resume, 0);
        });
        const state = {resume, pause};
        initializedNotices.set(notice, state);
        return state;
    }
    function setupDismissibleNotices(root = document) {
        root.querySelectorAll('[data-dismissible-notice]').forEach(notice => {
            if (!initializedNotices.has(notice)) prepareNotice(notice).resume();
        });
    }
    // Semua pesan tindakan menggunakan durasi dan tombol tutup yang sama.
    window.AyuNotice = {
        refresh: setupDismissibleNotices,
        show(notice, text) {
            if (!notice) return;
            const state = initializedNotices.get(notice);
            state?.pause();
            const close = notice.querySelector('[data-dismiss-notice]');
            const content = document.createElement('span');
            content.textContent = text;
            notice.replaceChildren(content);
            if (close) notice.append(close);
            notice.setAttribute('data-dismissible-notice', '');
            notice.setAttribute('data-notice-reusable', '');
            notice.hidden = false;
            prepareNotice(notice).resume();
        }
    };
    setupDismissibleNotices();
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form.customer-complete-form').forEach(form => {
            delete form.dataset.sending;
            const button = form.querySelector('button[type=submit]');
            button.disabled = false; button.textContent = 'Pesanan Selesai';
        });
    });
    document.addEventListener('ayu:content-updated', () => { updateForms(); setupCompletionForms(); setupDismissibleNotices(); });

    const endpoint = document.querySelector('script[data-notification-url]')?.dataset.notificationUrl;
    if (!endpoint || !document.querySelector('[data-notification-role]')) return;
    let busy = false;
    function render(role, state) {
        document.querySelectorAll(`[data-notification-role="${role}"]`).forEach(badge => {
            badge.textContent = state.count > 99 ? '99+' : String(state.count); badge.hidden = state.count === 0;
            badge.setAttribute('aria-label', state.count + ' pesanan dengan pembaruan belum dibaca');
        });
        document.querySelectorAll(`[data-notification-panel="${role}"]`).forEach(panel => {
            panel.hidden = state.count === 0;
            panel.querySelector('[data-notification-total]').textContent = state.count + ' pesanan belum dibaca';
            const snapshot = JSON.stringify(state.items);
            if (panel.dataset.snapshot === snapshot) return;
            panel.dataset.snapshot = snapshot;
            const list = panel.querySelector('ul'); list.replaceChildren();
            state.items.forEach(item => {
                const li=document.createElement('li'), a=document.createElement('a'), body=document.createElement('span'), heading=document.createElement('span');
                const number=document.createElement('strong'), time=document.createElement('time'), detail=document.createElement('span');
                a.className='notification-item'; a.href=item.url; body.className='notification-item__body'; heading.className='notification-item__heading';
                number.textContent=item.nomor; detail.className='notification-item__message'; detail.textContent=item.pesan;
                time.dateTime=item.waktu.replace(' ','T')+'+07:00';
                const date=new Date(time.dateTime);
                time.textContent=new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'short',timeZone:'Asia/Jakarta'}).format(date)
                    + ', ' + new Intl.DateTimeFormat('id-ID',{hour:'2-digit',minute:'2-digit',timeZone:'Asia/Jakarta'}).format(date);
                heading.append(number,time); body.append(heading,detail); a.append(body); li.append(a); list.append(li);
            });
            if (role === 'admin-pembayaran') document.dispatchEvent(new CustomEvent('ayu:payments-updated'));
        });
    }
    async function poll() {
        if (busy || document.hidden) return;
        busy = true;
        try {
            const response = await fetch(endpoint, {credentials:'same-origin',cache:'no-store',signal:AbortSignal.timeout(7000)});
            if (response.status === 401) {
                for (const role of ['pelanggan', 'admin', 'admin-pembayaran']) render(role, {count:0,items:[]});
                return;
            }
            if (!response.ok) return;
            const state=await response.json();
            for (const role of ['pelanggan','admin','admin-pembayaran']) if (state[role]) render(role,state[role]);
        } catch (_) { /* Pertahankan informasi terakhir ketika koneksi terputus. */ }
        finally { busy=false; }
    }
    poll(); setInterval(poll,15000);
    document.addEventListener('visibilitychange',poll); window.addEventListener('pageshow',poll);
    document.addEventListener('ayu:content-updated', poll);
})();
