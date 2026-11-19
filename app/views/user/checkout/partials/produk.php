<div class="co-items">
    <?php foreach ($data['items'] as $item): ?>

        <div class="co-product-row">
            <?php if ($item['gambar'] !== ''): ?>

                <img
                    src="<?= $aman(
                        BASE_URL
                        . '/public/assets/images/'
                        . $item['gambar']
                    ) ?>"
                    alt="<?= $aman($item['nama']) ?>"
                >

            <?php else: ?>

                <div class="co-product-placeholder">
                    Foto produk
                </div>

            <?php endif; ?>

            <div>
                <strong>
                    <?= $aman($item['nama']) ?>
                </strong>

                <p class="co-help">
                    <?= (int) $item['jumlah'] ?> loyang
                    × <?= $rupiah($item['harga']) ?>
                </p>
            </div>

            <strong>
                <?= $rupiah($item['subtotal']) ?>
            </strong>
        </div>

    <?php endforeach; ?>
</div>

<p class="co-help">
    <?= (int) $data['jumlahProduk'] ?> jenis produk
    • Total <?= (int) $data['jumlah'] ?> loyang
</p>