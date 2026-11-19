(() => {
    'use strict';
    // Perbarui isi modul; autentikasi, unduhan, dan perpindahan modul tetap memakai tautan asli.
    const groups = [
        ['admin-produk', 'admin-produk-form', 'admin-produk-hapus', 'admin-produk-pulihkan'],
        ['admin-kategori', 'admin-kategori-form', 'admin-kategori-hapus', 'admin-kategori-pulihkan'],
        ['admin-pesanan', 'admin-detail-pesanan'],
        ['admin-verifikasi', 'admin-periksa-pembayaran'],
        ['admin-laporan'], ['superadmin-laporan'],
        ['superadmin-faq'], ['superadmin-akun-admin'], ['superadmin-akun-pelanggan'],
        ['superadmin-profil-toko'],
        ['status-pesanan', 'detail-pesanan', 'riwayat-pesanan', 'batalkan-pesanan', 'selesai-pesanan']
    ];
    const ownScript = document.currentScript;
    const assets = new URL('../', ownScript.src);
    const localScripts = new Set(['admin-produk.js', 'admin-detail-pesanan.js',
        'superadmin-profil-toko.js', 'pesanan.js']);
    const registry = new Map();
    window.AyuUI = {
        register(name, initialize) {
            registry.get(name)?.cleanup?.();
            registry.set(name, {initialize, cleanup: initialize()});
        }
    };
    const root = () => document.querySelector('main');
    const groupOf = url => groups.find(group => group.includes(url.searchParams.get('page')));
    const localUrl = value => {
        const url = new URL(value, location.href);
        return url.origin === location.origin && url.pathname === location.pathname ? url : null;
    };
    const withinModule = url => url && groupOf(url) && groupOf(url) === groupOf(new URL(location.href));
    const motion = () => !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const submitted = new WeakMap();
    const listPositions = new Map();
    const positionKey = value => { const url = new URL(value); url.hash = ''; return url.href; };
    let active = null;
    let saving = false;

    const focus = element => {
        if (!element) return;
        if (!element.matches('a, input, select, textarea, button')) element.tabIndex = -1;
        element.focus({preventScroll: true});
    };
    function connectionError(wasSaving, attempted = location.href) {
        const main = root();
        main.querySelector('[data-module-error]')?.remove();
        const message = document.createElement('div');
        message.dataset.moduleError = '';
        message.className = 'ui-module-error';
        message.setAttribute('role', 'alert');
        message.textContent = wasSaving
            ? 'Hasil penyimpanan belum dapat dipastikan. Periksa kembali data sebelum menyimpan lagi. '
            : 'Bagian ini belum dapat dimuat. Silakan coba kembali. ';
        const retry = document.createElement('a');
        const url = new URL(wasSaving ? location.href : attempted);
        if (wasSaving) {
            url.search = new URLSearchParams({page: groupOf(url)[0]}).toString();
            url.hash = '';
        }
        retry.href = url.href;
        retry.textContent = wasSaving ? 'Periksa kembali data' : 'Muat ulang modul';
        message.append(retry);
        main.prepend(message);
        message.setAttribute('data-dismissible-notice', '');
        window.AyuNotice.refresh(main);
        focus(message);
    }
    async function stylesFrom(next) {
        const existing = new Set([...document.querySelectorAll('link[rel="stylesheet"]')].map(link => link.href));
        await Promise.all([...next.querySelectorAll('link[rel="stylesheet"]')].map(link => {
            const url = new URL(link.getAttribute('href'), location.href);
            if (existing.has(url.href) || url.origin !== location.origin
                || !url.pathname.startsWith(assets.pathname + 'css/')) return;
            return new Promise((resolve, reject) => {
                const style = document.createElement('link');
                style.rel = 'stylesheet'; style.href = url.href;
                const timer = setTimeout(() => { style.remove(); reject(new Error('Stylesheet timeout')); }, 10000);
                style.onload = () => { clearTimeout(timer); resolve(); };
                style.onerror = () => { clearTimeout(timer); style.remove(); reject(new Error('Stylesheet unavailable')); };
                document.head.append(style);
            });
        }));
    }
    async function scriptsFrom(next) {
        const existing = new Set([...document.scripts].map(script => script.src && new URL(script.src).pathname));
        for (const script of next.querySelectorAll('script[src]')) {
            const url = new URL(script.getAttribute('src'), location.href);
            const name = url.pathname.split('/').pop();
            if (url.origin !== location.origin || !url.pathname.startsWith(assets.pathname + 'js/')
                || !localScripts.has(name) || existing.has(url.pathname)) continue;
            existing.add(url.pathname);
            await new Promise((resolve, reject) => {
                const element = document.createElement('script');
                element.src = url.href;
                element.onload = resolve;
                element.onerror = () => { element.remove(); reject(new Error('Script unavailable')); };
                document.body.append(element);
            });
        }
    }
    async function navigate(url, {body = null, form = null, submitter = null, pop = false, refresh = false} = {}) {
        if (saving) return;
        active?.abort();
        const request = new AbortController();
        active = request;
        saving = body !== null;
        const wasSaving = saving;
        let expired = false;
        const timer = setTimeout(() => { expired = true; request.abort(); }, 30000);
        const previous = root();
        const previousUrl = new URL(location.href);
        const scroll = {x: scrollX, y: scrollY};
        if (previous.dataset.moduleScreen === 'list') {
            listPositions.set(positionKey(previous.dataset.moduleListUrl || previousUrl), scroll);
        }
        const scrollAreas = [...previous.querySelectorAll('[id], .ad-table-scroll')]
            .filter(element => element.scrollLeft > 0)
            .map(element => ({id: element.id, label: element.getAttribute('aria-label'), left: element.scrollLeft}));
        const files = form ? [...form.elements].filter(input => input.type === 'file' && input.files.length)
            .map(input => ({name: input.name, files: [...input.files]})) : [];
        const controls = wasSaving && form ? [...form.elements].filter(control => 'disabled' in control) : [];
        const disabled = controls.map(control => control.disabled);
        const initial = submitted.get(form);
        controls.forEach(control => { control.disabled = true; });
        if (submitter) { submitter.disabled = true; submitter.textContent = 'Memproses...'; }
        previous.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url.href, {method: wasSaving ? 'POST' : 'GET', body,
                credentials: 'same-origin', cache: 'no-store', signal: request.signal});
            const next = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (active !== request) return;
            const destination = localUrl(response.url);
            if (!withinModule(destination)) {
                if (destination && response.redirected) { location.assign(destination.href); return; }
                throw new Error('Unexpected module response');
            }
            const nextMain = next.querySelector('main');
            if (!nextMain) throw new Error('Module unavailable');
            await stylesFrom(next);
            if (active !== request) return;
            const resultUrl = wasSaving && !response.redirected ? new URL(location.href) : destination;
            if (!wasSaving) resultUrl.hash = url.hash;
            const sameList = previousUrl.searchParams.get('page') === resultUrl.searchParams.get('page')
                && !['id', 'edit', 'form', 'detail', 'nomor'].some(key => previousUrl.searchParams.has(key) || resultUrl.searchParams.has(key));
            if (sameList) {
                const listPanel = previous.querySelector('.ad-table-scroll')?.closest('.ad-panel')
                    || previous.querySelector('[data-module-min-height]');
                const nextPanel = nextMain.querySelector('.ad-table-scroll')?.closest('.ad-panel')
                    || nextMain.querySelector('.ad-panel');
                if (listPanel && nextPanel) {
                    nextPanel.style.minHeight = listPanel.offsetHeight + 'px';
                    nextPanel.dataset.moduleMinHeight = '';
                }
            }
            if (!pop && !refresh) {
                history.replaceState({...history.state, ayuScroll: scroll}, '', location.href);
                history[wasSaving ? 'replaceState' : 'pushState']({}, '', resultUrl.href);
            }
            for (const entry of registry.values()) entry.cleanup?.();
            previous.replaceWith(nextMain);
            nextMain.setAttribute('aria-busy', 'true');
            window.scrollTo({left: scroll.x, top: scroll.y, behavior: 'instant'});
            document.title = next.title;
            for (const entry of registry.values()) entry.cleanup = entry.initialize();
            await scriptsFrom(next);
            if (active !== request) return;
            // Profil/nama dan jumlah keranjang ikut diperbarui tanpa mengikat ulang menu header.
            for (const selector of ['.ad-brand', '.ad-admin-name', '.brand-copy', '.ac-account__intro', '[data-header-cart]']) {
                const current = document.querySelector(selector), updated = next.querySelector(selector);
                if (current && updated) {
                    if (selector === '[data-header-cart]') {
                        const badge = current.querySelector('[data-cart-badge]');
                        const nextBadge = updated.querySelector('[data-cart-badge]');
                        if (badge && nextBadge) { badge.textContent = nextBadge.textContent; badge.hidden = nextBadge.hidden; }
                    } else current.replaceChildren(...updated.childNodes);
                    if (updated.hasAttribute('aria-label')) current.setAttribute('aria-label', updated.getAttribute('aria-label'));
                }
            }
            document.dispatchEvent(new CustomEvent('ayu:content-updated', {detail: {root: nextMain}}));
            const error = nextMain.querySelector('[role="alert"], .ad-form-error');
            if (error && files.length) {
                for (const saved of files) {
                    const input = [...nextMain.querySelectorAll('input[type="file"]')].find(input => input.name === saved.name);
                    if (!input) continue;
                    const transfer = new DataTransfer();
                    saved.files.forEach(file => transfer.items.add(file));
                    input.files = transfer.files;
                    input.dispatchEvent(new Event('change', {bubbles: true}));
                }
            }
            for (const area of scrollAreas) {
                const element = area.id ? document.getElementById(area.id)
                    : [...nextMain.querySelectorAll('.ad-table-scroll')].find(element => element.getAttribute('aria-label') === area.label);
                if (element) element.scrollLeft = area.left;
            }
            const target = resultUrl.hash ? document.getElementById(resultUrl.hash.slice(1)) : null;
            if (target && !pop) {
                focus(target.querySelector('[data-sa-form-focus]') || target);
                target.scrollIntoView({behavior: motion() ? 'smooth' : 'instant', block: 'start'});
            } else {
                const screenChanged = nextMain.dataset.moduleScreen && (
                    nextMain.dataset.moduleScreen !== previous.dataset.moduleScreen
                    || resultUrl.search !== previousUrl.search);
                const screenTop = {x: 0, y: Math.max(0, nextMain.offsetTop - 24)};
                const position = pop ? history.state?.ayuScroll || scroll
                    : screenChanged && nextMain.dataset.moduleScreen !== 'list' ? screenTop
                    : screenChanged && previous.dataset.moduleScreen !== 'list'
                        ? (wasSaving ? screenTop : listPositions.get(positionKey(nextMain.dataset.moduleListUrl || resultUrl)) || screenTop)
                        : scroll;
                window.scrollTo({left: position.x, top: position.y, behavior: 'instant'});
                const success = nextMain.querySelector('[role="status"]');
                focus(error || (wasSaving ? success : null)
                    || (screenChanged ? nextMain.querySelector('h1') : null));
            }
            if (motion() && nextMain.animate) nextMain.animate([{opacity: .35}, {opacity: 1}], {duration: 140, easing: 'ease-out'});
        } catch (error) {
            if ((error.name !== 'AbortError' || expired) && active === request) {
                if (wasSaving && form) form.dataset.moduleUncertain = 'true';
                connectionError(wasSaving, url.href);
            }
        } finally {
            clearTimeout(timer);
            if (active === request) {
                active = null; saving = false;
                previous.removeAttribute('aria-busy');
                root()?.removeAttribute('aria-busy');
                controls.forEach((control, index) => {
                    if (control.isConnected) control.disabled = control === submitter ? initial?.disabled ?? false : disabled[index];
                });
                if (submitter?.isConnected) {
                    if (initial) submitter.innerHTML = initial.html;
                    else submitter.textContent = 'Coba kembali';
                }
                if (form?.isConnected) delete form.dataset.sending;
            }
        }
    }
    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || !root()?.contains(link) || event.defaultPrevented || event.button !== 0
            || link.target || link.hasAttribute('download') || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = localUrl(link.href);
        if (!withinModule(url) || (url.href.split('#')[0] === location.href.split('#')[0] && url.hash && !url.searchParams.has('edit') && !url.searchParams.has('detail') && !url.searchParams.has('form'))) return;
        event.preventDefault();
        if (!saving) navigate(url);
    });
    document.addEventListener('ayu:payments-updated', () => {
        if (new URL(location.href).searchParams.get('page') !== 'admin-verifikasi'
            || saving || root()?.hasAttribute('aria-busy')
            || root()?.contains(document.activeElement) && document.activeElement.matches('input, select, textarea')) return;
        navigate(new URL(location.href), {refresh: true});
    });
    document.addEventListener('submit', event => {
        if (event.submitter) submitted.set(event.target, {html: event.submitter.innerHTML, disabled: event.submitter.disabled});
    }, true);
    document.addEventListener('submit', event => {
        const form = event.target;
        if (event.defaultPrevented || !root()?.contains(form) || form.target) return;
        let url = localUrl(form.action || location.href);
        const data = new FormData(form);
        if (event.submitter?.name) data.append(event.submitter.name, event.submitter.value);
        if (form.method.toLowerCase() === 'get' && url) {
            url.search = new URLSearchParams(data).toString();
        }
        if (!withinModule(url)) return;
        event.preventDefault();
        if (saving) return;
        if (form.dataset.moduleUncertain) { connectionError(true); return; }
        navigate(url, {body: form.method.toLowerCase() === 'post' ? data : null,
            form, submitter: event.submitter});
    });
    window.addEventListener('popstate', () => {
        const url = localUrl(location.href);
        if (withinModule(url) && !saving) navigate(url, {pop: true});
    });
    window.addEventListener('resize', () => {
        root()?.querySelectorAll('[data-module-min-height]').forEach(panel => {
            panel.style.minHeight = ''; delete panel.dataset.moduleMinHeight;
        });
    });
})();
