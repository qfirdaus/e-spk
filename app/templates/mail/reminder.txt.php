<?php
// Template: reminder.txt.php
// Vars expected:
// $targetNama, $stafNama, $stafNopek, $tahun
// (optional) $jabatanNama, $deadline, $actionUrl, $systemName, $footerNote

$systemName  = $systemName  ?? 'e-Prestasi';
$targetNama  = $targetNama  ?? 'Tuan/Puan';
$stafNama    = $stafNama    ?? '';
$stafNopek   = $stafNopek   ?? '';
$jabatanNama = $jabatanNama ?? '';
$deadline    = $deadline    ?? '';
$actionUrl   = $actionUrl   ?? '';
$footerNote  = $footerNote  ?? 'Emel ini dijana secara automatik. Sila jangan balas emel ini.';
?>

═══════════════════════════════════════════════════════════════
                    PERINGATAN PENILAIAN SKT
═══════════════════════════════════════════════════════════════

Assalamualaikum W.B.T / Salam sejahtera,

Yang Berhormat/Yang Berbahagia Datuk/Dato'/Prof. Emeritus/Prof./Prof. Madya/Dr./Ts./Tuan/Puan

<?= $targetNama ?>,

Dengan hormatnya, perkara di atas adalah dirujuk.

Adalah dimaklumkan bahawa pihak Yang Berhormat/Yang Berbahagia perlu melengkapkan penilaian 
Sasaran Kerja Tahunan (SKT) bagi staf yang berada di bawah seliaan Yang Berhormat/Yang Berbahagia. 
Kerjasama Yang Berhormat/Yang Berbahagia dalam memastikan penilaian ini dilengkapkan adalah amat dihargai.

───────────────────────────────────────────────────────────────
                        MAKLUMAT STAF
───────────────────────────────────────────────────────────────

Nama Staf     : <?= $stafNama . PHP_EOL ?>
No. Staf      : <?= $stafNopek . PHP_EOL ?>
Tahun         : <?= $tahun . PHP_EOL ?>
<?php if ($jabatanNama): ?>
Jabatan       : <?= $jabatanNama . PHP_EOL ?>
<?php endif; ?>
<?php if ($deadline): ?>
Tarikh Akhir  : <?= $deadline . PHP_EOL ?>
<?php endif; ?>

───────────────────────────────────────────────────────────────
                          TINDAKAN
───────────────────────────────────────────────────────────────

<?php if ($actionUrl): ?>
Sila klik pautan di bawah untuk terus ke halaman penilaian:

    <?= $actionUrl . PHP_EOL ?>

<?php else: ?>
Sila log masuk ke sistem <?= $systemName ?> untuk melengkapkan 
penilaian SKT.

<?php endif; ?>
───────────────────────────────────────────────────────────────

Kerjasama Yang Berhormat/Yang Berbahagia dalam memastikan penilaian SKT dilengkapkan tepat 
pada masanya amat dihargai.

Sekian, terima kasih.

Yang benar,
Sistem <?= $systemName ?>


═══════════════════════════════════════════════════════════════

<?= $footerNote . PHP_EOL ?>
