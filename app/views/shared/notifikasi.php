<?php
$ringkasan = $notifikasi;
$amanNotifikasi = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$waktuNotifikasi = static function (string $s): string {
    $waktu = new DateTimeImmutable($s, new DateTimeZone('Asia/Jakarta'));
    $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return $waktu->format('j') . ' ' . $bulan[(int) $waktu->format('n')] . ', ' . $waktu->format('H.i');
};
?>
<section class="notification-panel" data-notification-panel="<?= htmlspecialchars($peranNotifikasi, ENT_QUOTES, 'UTF-8') ?>" data-snapshot="<?= $amanNotifikasi(json_encode($ringkasan['items'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>" <?= $ringkasan['count'] === 0 ? 'hidden' : '' ?>>
    <div class="notification-panel__heading">
        <span class="notification-panel__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"></path></svg></span>
        <div class="notification-panel__intro">
            <h2 class="notification-panel__title"><?= $amanNotifikasi($judulNotifikasi ?? 'Pembaruan pesanan') ?></h2>
            <p class="notification-panel__description"><?= $amanNotifikasi($deskripsiNotifikasi ?? 'Buka pesanan untuk melihat pembaruan terbaru.') ?></p>
        </div>
        <span class="notification-panel__total" data-notification-total><?= (int) $ringkasan['count'] ?> pesanan belum dibaca</span>
    </div>
    <ul class="notification-list">
        <?php foreach ($ringkasan['items'] as $item): ?>
            <li><a class="notification-item" href="<?= $amanNotifikasi($item['url']) ?>">
                <span class="notification-item__body">
                    <span class="notification-item__heading"><strong><?= $amanNotifikasi($item['nomor']) ?></strong><time datetime="<?= $amanNotifikasi(str_replace(' ', 'T', $item['waktu']) . '+07:00') ?>"><?= $amanNotifikasi($waktuNotifikasi($item['waktu'])) ?></time></span>
                    <span class="notification-item__message"><?= $amanNotifikasi($item['pesan']) ?></span>
                </span>
            </a></li>
        <?php endforeach; ?>
    </ul>
</section>
