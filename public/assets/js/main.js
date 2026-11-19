(() => {
    'use strict';
    // Back/Forward setelah simpan/checkout harus membaca keadaan sesi terbaru,
    // termasuk tombol submit dan keranjang yang telah dikosongkan.
    window.addEventListener('pageshow', event => {
        if (event.persisted && document.querySelector(
            '.kb-page, .co-page, .po-page, .rh-page, .pb-page'
        )) window.location.reload();
    });
    const link = document.querySelector('[data-header-cart]');
    const badge = document.querySelector('[data-cart-badge]');
    if (!link || !badge) return;
    const update = count => {
        const quantity = Number(count);
        if (!Number.isSafeInteger(quantity) || quantity < 0) return;
        badge.textContent = quantity > 99 ? '99+' : String(quantity);
        badge.hidden = quantity === 0;
        link.setAttribute('aria-label', 'Keranjang, ' + quantity + ' loyang');
    };
    document.addEventListener('ayu:cart-updated', event => update(event.detail?.jumlahLoyang));
    // Pulihkan angka terbaru ketika kembali dari checkout atau tab lain.
    async function refresh() {
        try {
            const response = await fetch(link.dataset.cartSummaryUrl, {credentials: 'same-origin', cache: 'no-store'});
            if (!response.ok) return;
            const data = await response.json();
            if (data.berhasil) update(data.jumlahLoyang);
        } catch (_) { /* Badge awal dari server tetap dipakai saat jaringan gagal. */ }
    }
    window.addEventListener('pageshow', event => { if (event.persisted) refresh(); });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
})();
