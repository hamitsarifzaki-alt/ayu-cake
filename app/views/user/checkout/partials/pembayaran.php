<div data-checkout-payment>
    <p class="co-help">Metode pembayaran: <strong>Transfer Bank</strong></p>
    <label class="co-field co-payment-field co-payment-wallet-field" id="co-payment-wallet-field" for="co-payment-wallet">
        <span>Bank tujuan *</span>
        <select id="co-payment-wallet" name="pembayaran" data-payment-wallet required<?= !$data['bankTersedia'] ? ' disabled' : '' ?>>
            <option value="">Pilih bank</option>
            <?php foreach ($data['bankTersedia'] as $kode => $akun): ?>
                <option value="bank-<?= $aman($kode) ?>"><?= $aman($akun['nama']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</div>
<?php if (!$data['bankTersedia']): ?><p class="co-help">Rekening bank belum tersedia. Hubungi toko sebelum melakukan pemesanan.</p><?php endif; ?>
