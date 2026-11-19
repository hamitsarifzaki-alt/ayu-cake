(() => {
    "use strict";

    const button = document.querySelector("[data-cart-add]");
    const quantityInput = document.getElementById("dp-jumlah");
    const quantityMessage = document.getElementById("dp-pesan-jumlah");
    const notification = document.getElementById("dp-pemberitahuan");

    if (!button || !quantityInput || !notification) {
        return;
    }

    function showMessage(text, isError = false) {
        window.AyuNotice.show(notification, text);
        notification.classList.toggle("dp-notice-error", isError);
    }

    button.addEventListener("click", async () => {
        if (button.disabled) return;

        const quantity = Number(quantityInput.value);

        if (!Number.isSafeInteger(quantity) || quantity < 1) {
            const text =
                "Masukkan jumlah loyang berupa bilangan bulat minimal 1.";

            if (quantityMessage) {
                quantityMessage.textContent = text;
            }

            quantityInput.setAttribute("aria-invalid", "true");
            quantityInput.focus();

            showMessage(text, true);
            return;
        }

        quantityInput.removeAttribute("aria-invalid");

        if (quantityMessage) {
            quantityMessage.textContent = "";
        }

        notification.hidden = true;

        const originalText = button.textContent;

        button.disabled = true;
        button.setAttribute("aria-busy", "true");
        button.textContent = "Menambahkan...";

        const body = new FormData();

        body.set("token", button.dataset.token);
        body.set("id", button.dataset.id);
        body.set("jumlah", String(quantity));

        try {
            const response = await fetch(button.dataset.url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json"
                },
                body
            });

            const result = await response.json();

            if (!response.ok || !result.berhasil) {
                throw new Error(
                    result.pesan || "Produk belum dapat ditambahkan."
                );
            }

            document.dispatchEvent(new CustomEvent('ayu:cart-updated', {
                detail: { jumlahLoyang: result.jumlahLoyang }
            }));
            showMessage(
                result.pesan + " Kamu bisa melanjutkan memilih kue."
            );
        } catch (error) {
            showMessage(
                error instanceof SyntaxError
                    ? "Respons sistem tidak sesuai. Silakan muat ulang halaman."
                    : error.message || "Terjadi kesalahan. Silakan coba kembali.",
                true
            );
        } finally {
            button.disabled = false;
            button.removeAttribute("aria-busy");
            button.textContent = originalText;
        }
    });
})();
