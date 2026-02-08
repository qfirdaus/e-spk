<?php
// Template: acknowledgement.txt.php
// Vars expected:
// $targetNama, $peranan, $stafNama, $stafNopek, $tahun
// (optional) $markPPP, $markPPK, $purata, $bulanGajiText, $submittedAt, $reviewUrl,
//            $jabatanNama, $systemName, $footerNote

$systemName   = $systemName   ?? 'e-Prestasi';
$targetNama   = $targetNama   ?? '';
$peranan      = $peranan      ?? '';
$stafNama     = $stafNama     ?? '';
$stafNopek    = $stafNopek    ?? '';
$jabatanNama  = $jabatanNama  ?? '';
$bulanGajiTxt = $bulanGajiText ?? '';
$submittedAt  = $submittedAt  ?? '';
$reviewUrl    = $reviewUrl    ?? '';
$footerNote   = $footerNote   ?? 'Emel ini dijana secara automatik. Sila jangan balas emel ini.';

$markPPP = isset($markPPP) ? (string)$markPPP : '';
$markPPK = isset($markPPK) ? (string)$markPPK : '';
$purata  = isset($purata)  ? (string)$purata  : '';
?>

═══════════════════════════════════════════════════════════════
                  PENGESAHAN PENILAIAN SKT
═══════════════════════════════════════════════════════════════

Assalamualaikum W.B.T / Salam sejahtera,

Yang Berhormat/Yang Berbahagia Datuk/Dato'/Prof. Emeritus/Prof./Prof. Madya/Dr./Ts./Tuan/Puan

<?= $targetNama ?>,

Dengan hormatnya, perkara di atas adalah dirujuk.

Adalah dimaklumkan bahawa rekod penilaian Sasaran Kerja Tahunan (SKT) telah 
dihantar/dikemas kini oleh <?= $peranan ?: 'penilai' ?><?php if ($submittedAt): ?> pada <?= $submittedAt ?><?php endif; ?>.

───────────────────────────────────────────────────────────────
                        MAKLUMAT STAF
───────────────────────────────────────────────────────────────

Nama Staf     : <?= $stafNama . PHP_EOL ?>
No. Staf      : <?= $stafNopek . PHP_EOL ?>
Tahun         : <?= $tahun . PHP_EOL ?>
<?php if ($jabatanNama): ?>
Jabatan       : <?= $jabatanNama . PHP_EOL ?>
<?php endif; ?>
<?php if ($bulanGajiTxt): ?>
Bulan Gaji    : <?= $bulanGajiTxt . PHP_EOL ?>
<?php endif; ?>
<?php if ($markPPP !== '' || $markPPK !== '' || $purata !== ''): ?>

Markah PPP    : <?= $markPPP . PHP_EOL ?>
Markah PPK    : <?= $markPPK . PHP_EOL ?>
Purata        : <?= $purata . PHP_EOL ?>
<?php endif; ?>

───────────────────────────────────────────────────────────────
                          TINDAKAN
───────────────────────────────────────────────────────────────

<?php if ($reviewUrl): ?>
Untuk semakan lanjut, sila klik pautan di bawah:

    <?= $reviewUrl . PHP_EOL ?>

<?php endif; ?>
───────────────────────────────────────────────────────────────

Terima kasih diucapkan kepada Yang Berhormat/Yang Berbahagia atas kerjasama 
dalam melengkapkan penilaian SKT ini.

Sekian, terima kasih.

Yang benar,
Sistem <?= $systemName ?>


═══════════════════════════════════════════════════════════════

<?= $footerNote . PHP_EOL ?>
