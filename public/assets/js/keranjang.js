(() => {
    "use strict";

    const configElement = document.getElementById("kb-config");
    if (!configElement) return;

    const config = JSON.parse(configElement.textContent);

    let savedSummary = config.ringkasan;
    let summary = savedSummary;
    // Simpan nilai terbaru per produk dan kirim berurutan.
    const pending = new Map();
    let saving = false;
    let timer;
    let failed = false;

    const message = document.getElementById("kb-message");
    const filled = document.getElementById("kb-filled");
    const empty = document.getElementById("kb-empty");
    const syncStatus = document.getElementById("kb-sync-status");
    const retry = document.getElementById("kb-retry");
    const toast = document.getElementById("kb-toast");

    function rupiah(value) {
        return "Rp" + new Intl.NumberFormat("id-ID", {
            maximumFractionDigits: 0
        }).format(value);
    }

    function showMessage(text) {
        if (!message) return;

        if (toast) toast.hidden = true;
        if (window.AyuNotice) window.AyuNotice.show(message, text);
        else { message.textContent = text; message.hidden = false; }
    }

    function showRemovalNotice() {
        if (!toast) return;
        const text = "Produk berhasil dihapus dari keranjang.";
        if (window.AyuNotice) window.AyuNotice.show(toast, text);
        else { toast.textContent = text; toast.hidden = false; }
    }

    function render() {
        const items = savedSummary.items.flatMap(item => {
            const id = Number(item.id);
            const quantity = pending.has(id) ? pending.get(id) : Number(item.jumlah);
            return quantity === null ? [] : [{
                ...item, jumlah: quantity, subtotal: Number(item.harga) * quantity
            }];
        });
        summary = {
            items,
            jumlahProduk: items.length,
            jumlahLoyang: items.reduce((total, item) => total + item.jumlah, 0),
            subtotal: items.reduce((total, item) => total + item.subtotal, 0)
        };
        document.dispatchEvent(new CustomEvent('ayu:cart-updated', {
            detail: { jumlahLoyang: summary.jumlahLoyang }
        }));
        const itemsById = new Map(
            summary.items.map(item => [Number(item.id), item])
        );

        document.querySelectorAll("[data-cart-item]").forEach(row => {
            const item = itemsById.get(Number(row.dataset.id));

            if (!item) {
                row.hidden = true;
                return;
            }
            row.hidden = false;

            row.querySelector("[data-cart-quantity]").textContent =
                item.jumlah;

            row.querySelector("[data-cart-subtotal]").textContent =
                rupiah(item.subtotal);

            const stock = row.querySelector("[data-cart-stock]");
            if (stock) {
                stock.textContent = `Stok siap: ${Math.max(0, Number(item.stok))} loyang`;
            }

            row.querySelector("[data-cart-minus]").disabled =
                Number(item.jumlah) <= 1;

            row.querySelector("[data-cart-plus]").disabled =
                !Number.isSafeInteger(item.jumlah + 1);
        });

        document.getElementById("kb-badge").textContent =
            `${summary.jumlahProduk} produk • ${summary.jumlahLoyang} loyang`;

        document.getElementById("kb-product-count").textContent =
            `${summary.jumlahProduk} produk`;

        document.getElementById("kb-quantity-count").textContent =
            `${summary.jumlahLoyang} loyang`;

        document.getElementById("kb-total").textContent =
            rupiah(summary.subtotal);

        const isEmpty = summary.items.length === 0;

        filled.hidden = isEmpty;
        empty.hidden = !isEmpty;

        const busy = saving || pending.size > 0;
        syncStatus.textContent = failed
            ? "Perubahan belum tersimpan."
            : busy ? "Menyimpan perubahan…" : "";
        retry.hidden = !failed;
        document.querySelectorAll("[data-cart-checkout]").forEach(button => {
            button.disabled = busy || isEmpty;
        });
    }

    function updateCart(id, action, quantity) {
        pending.set(id, action === "hapus" ? null : quantity);
        failed = false;
        message.hidden = true;
        render();
        clearTimeout(timer);
        timer = setTimeout(saveNext, 180);
    }

    async function saveNext() {
        if (saving || failed || pending.size === 0) return;
        const [id, quantity] = pending.entries().next().value;
        const action = quantity === null ? "hapus" : "jumlah";
        saving = true;
        render();
        const body = new FormData();

        body.set("token", config.token);
        body.set("id", String(id));
        body.set("aksi", action);

        if (action === "jumlah") {
            body.set("jumlah", String(quantity));
        }

        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 12000);
        try {
            const response = await fetch(config.urlUbah, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json"
                },
                body,
                signal: controller.signal
            });

            const result = await response.json();

            if (!response.ok || !result.berhasil) {
                throw new Error(
                    result.pesan || "Keranjang belum dapat diperbarui."
                );
            }

            savedSummary = result.ringkasan;
            if (action === "hapus") showRemovalNotice();
            // Respons lama tidak boleh menghapus klik yang lebih baru.
            if (pending.get(id) === quantity) pending.delete(id);
        } catch (error) {
            failed = true;
            showMessage(
                error instanceof SyntaxError
                    ? "Respons sistem tidak sesuai. Silakan muat ulang halaman."
                    : error.name === "AbortError"
                        ? "Koneksi terlalu lama. Coba simpan kembali sebelum checkout."
                        : error.message || "Perubahan belum tersimpan. Silakan coba lagi."
            );
        } finally {
            clearTimeout(timeout);
            saving = false;
            render();
            if (!failed && pending.size > 0) saveNext();
        }
    }

    retry.addEventListener("click", () => {
        failed = false;
        message.hidden = true;
        saveNext();
    });
    // Tombol tambah, kurang, dan hapus produk.
    document.querySelectorAll("[data-cart-item]").forEach(row => {
        const id = Number(row.dataset.id);

        function currentItem() {
            return summary.items.find(item => Number(item.id) === id);
        }

        row.querySelector("[data-cart-minus]").addEventListener(
            "click",
            event => {
                event.preventDefault();

                const item = currentItem();
                if (!item) return;

                const quantity = Number(item.jumlah);

                if (Number.isSafeInteger(quantity) && quantity > 1) {
                    updateCart(id, "jumlah", quantity - 1);
                }
            }
        );

        row.querySelector("[data-cart-plus]").addEventListener(
            "click",
            event => {
                event.preventDefault();

                const item = currentItem();
                if (!item) return;

                const quantity = Number(item.jumlah);

                if (
                    Number.isSafeInteger(quantity)
                    && Number.isSafeInteger(quantity + 1)
                ) {
                    updateCart(id, "jumlah", quantity + 1);
                }
            }
        );

        row.querySelector("[data-cart-remove]").addEventListener(
            "click",
            event => {
                event.preventDefault();
                updateCart(id, "hapus");
            }
        );
    });

    // Checkout seluruh isi keranjang.
    document.querySelectorAll("[data-cart-checkout]").forEach(button => {
        button.addEventListener("click", event => {
            event.preventDefault();

            if (saving || pending.size > 0 || summary.items.length === 0) return;

            const type = button.dataset.cartCheckout;

            if (type !== "booking" && type !== "pesan-sekarang") {
                showMessage("Jenis pemesanan tidak dikenali.");
                return;
            }

            // Batas Booking dihitung dari total loyang semua produk.
            if (type === "booking" && Number(summary.jumlahLoyang) > 50) {
                showMessage(
                    "Total Booking maksimal 50 loyang per hari. " +
                    "Kurangi jumlah produk sebelum melanjutkan."
                );
                return;
            }

            // Pesan Sekarang memeriksa stok setiap produk.
            if (type === "pesan-sekarang") {
                const unavailable = summary.items.find(
                    item => Number(item.jumlah) > Number(item.stok)
                );

                if (unavailable) {
                    showMessage(
                        `Stok siap ${unavailable.nama} hanya ` +
                        `${unavailable.stok} loyang. Kurangi jumlah ` +
                        "atau gunakan Booking."
                    );
                    return;
                }
            }

            const destination = new URL(
                config.urlDasar,
                window.location.href
            );

            // Bersihkan parameter produk tunggal jika sebelumnya ada.
            destination.searchParams.delete("id");
            destination.searchParams.delete("jumlah");

            destination.searchParams.set("page", type);
            destination.searchParams.set("sumber", "keranjang");

            // CheckoutController membaca seluruh produk dari sesi keranjang.
            window.location.assign(destination.href);
        });
    });

    render();
})();
