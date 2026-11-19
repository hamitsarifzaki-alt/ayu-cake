(() => {
    'use strict';
    const page = document.querySelector('.catalog-page');
    if (!page) return;
    const form = page.querySelector('.catalog-filter');
    const cards = Array.from(page.querySelectorAll('.catalog-card'));
    const empty = page.querySelector('.catalog-filter-empty');
    const pagination = page.querySelector('.catalog-pagination');
    const query = form.querySelector('[name="q"]');
    const categoryIntro = page.querySelector('[data-category-intro]');
    const categoryTitle = page.querySelector('[data-category-title]');
    const description = page.querySelector('[data-category-description]');
    const descriptions = JSON.parse(document.getElementById('category-descriptions')?.textContent || '{}');
    const headerSearch = document.querySelector('.search-form');
    let summary = page.querySelector('.catalog-search-summary');
    if (!summary) {
        summary = document.createElement('div');
        summary.className = 'catalog-search-summary';
        const text = document.createElement('p');
        text.append('Hasil pencarian: ', document.createElement('strong'));
        const clear = document.createElement('a');
        clear.textContent = 'Hapus pencarian';
        summary.append(text, clear);
        page.querySelector('.catalog-products-panel').before(summary);
    }
    function render(updateAddress = false) {
        const selectedCategory = form.querySelector('[name="kategori"]:checked');
        const category = selectedCategory?.value || 'semua';
        if (categoryIntro) categoryIntro.hidden = category === 'semua';
        if (categoryTitle) categoryTitle.textContent = category === 'semua'
            ? '' : selectedCategory?.closest('label')?.querySelector('span')?.textContent.trim() || '';
        if (description) { description.textContent = descriptions[category] || ''; description.hidden = !descriptions[category]; }
        const type = form.querySelector('[name="jenis"]:checked')?.value || 'semua';
        const keyword = (query.value || '').trim().toLocaleLowerCase('id-ID');
        summary.hidden = keyword === '';
        summary.querySelector('strong').textContent = '“' + query.value.trim() + '”';
        if (headerSearch) headerSearch.querySelector('[name="q"]').value = query.value;
        let count = 0;
        cards.forEach(card => {
            const name = card.querySelector('h3').textContent.trim().toLocaleLowerCase('id-ID');
            card.hidden = !((category === 'semua' || card.dataset.kategori === category)
                && (type === 'semua' || card.dataset.jenis === type)
                && (keyword === '' || name.includes(keyword)));
            if (!card.hidden) count++;
        });
        if (empty) empty.hidden = count > 0;
        if (pagination) pagination.hidden = count === 0;
        if (updateAddress) {
            const url = new URL(location.href);
            for (const [key, value] of [['kategori', category], ['jenis', type]]) {
                if (value === 'semua') url.searchParams.delete(key);
                else url.searchParams.set(key, value);
            }
            if (keyword === '') url.searchParams.delete('q');
            else url.searchParams.set('q', query.value.trim());
            history.replaceState(null, '', url);
            const clear = page.querySelector('.catalog-search-summary a');
            if (clear) {
                const clearUrl = new URL(url);
                clearUrl.searchParams.delete('q');
                clear.href = clearUrl.href;
            }
        }
    }
    form.querySelectorAll('[name="kategori"], [name="jenis"]').forEach(input => {
        input.addEventListener('change', () => render(true));
    });
    form.addEventListener('submit', event => { event.preventDefault(); render(true); });
    headerSearch?.addEventListener('submit', event => {
        event.preventDefault();
        query.value = headerSearch.querySelector('[name="q"]').value;
        render(true);
    });
    summary.querySelector('a').addEventListener('click', event => {
        event.preventDefault(); query.value = ''; render(true); query.focus({preventScroll: true});
    });
    render();
})();
