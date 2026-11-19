<div class="po-notice po-notice-gold" role="status">
    <strong>Admin menolak bukti pembayaran.</strong>
    <p>Alasan: <?= nl2br($aman($pesanan['penolakan_bukti']['catatan'])) ?></p>
    <p>Perbaiki bukti sesuai alasan admin, lalu unggah ulang sebelum batas waktu pembayaran.</p>
    <?php if (!empty($pesanan['batas_pembayaran'])): ?>
        <p>Batas unggah ulang: <strong><?= $aman($tanggal($pesanan['batas_pembayaran'], true)) ?></strong></p>
    <?php endif; ?>
</div>
