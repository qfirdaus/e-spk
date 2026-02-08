<?php
// Template: acknowledgement.html.php
// Vars expected:
// $targetNama, $peranan, $stafNama, $stafNopek, $tahun
// (optional) $markPPP, $markPPK, $purata, $bulanGajiText, $submittedAt, $reviewUrl,
//            $jabatanNama, $systemName, $footerNote

if (!function_exists('e')) {
  function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$systemName   = $systemName   ?? 'e-Prestasi';
$title        = "Pengesahan Penilaian SKT " . e($tahun);
$targetNama   = $targetNama   ?? '';
$peranan      = $peranan      ?? '';     // PPP / PPK
$stafNama     = $stafNama     ?? '';
$stafNopek    = $stafNopek    ?? '';
$jabatanNama  = $jabatanNama  ?? '';
$bulanGajiTxt = $bulanGajiText ?? '';
$submittedAt  = $submittedAt  ?? '';     // contoh '13 Ogos 2025, 4:32 PM'
$reviewUrl    = $reviewUrl    ?? '';
$footerNote   = $footerNote   ?? 'Emel ini dijana secara automatik. Sila jangan balas emel ini.';

$markPPP = isset($markPPP) ? (string)$markPPP : '';
$markPPK = isset($markPPK) ? (string)$markPPK : '';
$purata  = isset($purata)  ? (string)$purata  : '';
?>
<!doctype html>
<html lang="ms">
<head>
  <meta charset="utf-8">
  <title><?= $title ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#222;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:transparent;padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width:640px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.12);">
          <!-- Header -->
          <tr>
            <td style="background:#16a34a;color:#fff;padding:24px 28px;text-align:center;">
              <div style="font-size:24px;font-weight:700;margin-bottom:4px;letter-spacing:-0.5px;">
                <span style="font-size:28px;vertical-align:middle;margin-right:8px;">&#10004;</span>PENGESAHAN PENILAIAN SKT
              </div>
              <div style="font-size:14px;opacity:0.95;margin-top:4px;">
                <?= e($systemName) ?> • Tahun <?= e($tahun) ?>
              </div>
            </td>
          </tr>
          
          <!-- Content -->
          <tr>
            <td style="padding:28px 28px 20px 28px;">
              <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;">
                Assalamualaikum W.B.T / Salam sejahtera,
              </p>
              <p style="margin:0 0 0 0;font-size:16px;line-height:1.6;">
                Yang Berhormat/Yang Berbahagia Datuk/Dato'/Prof. Emeritus/Prof./Prof. Madya/Dr./Ts./Tuan/Puan
              </p>
              <p style="margin:0 0 16px 0;font-size:16px;line-height:1.6;">
                <strong><?= e($targetNama ?: '') ?></strong>,
              </p>
              <p style="margin:0 0 24px 0;font-size:15px;line-height:1.7;color:#374151;">
                Dengan hormatnya, perkara di atas adalah dirujuk.
              </p>
              <p style="margin:0 0 24px 0;font-size:15px;line-height:1.7;color:#374151;">
                Adalah dimaklumkan bahawa rekod penilaian <strong>Sasaran Kerja Tahunan (SKT)</strong> telah 
                <strong>dihantar/dikemas kini</strong> oleh <?= $peranan ? '<strong>'.e($peranan).'</strong>' : 'penilai' ?>.
                <?php if ($submittedAt): ?>
                  <span style="color:#6b7280;">(<?= e($submittedAt) ?>)</span>
                <?php endif; ?>
              </p>

              <!-- Info Card -->
              <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;margin:0 0 24px 0;border-collapse:collapse;background:#f8f9fa;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">
                <tr>
                  <td style="padding:20px 24px;">
                    <div style="font-size:13px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:16px;border-bottom:2px solid #e5e7eb;padding-bottom:8px;">
                      <span style="font-size:16px;vertical-align:middle;margin-right:6px;">&#128203;</span>Maklumat Staf
                    </div>
                    <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
                      <tr>
                        <td style="padding:8px 0;width:140px;color:#6b7280;font-size:14px;vertical-align:top;">Nama Staf</td>
                        <td style="padding:8px 0;font-size:15px;font-weight:600;color:#111827;"><?= e($stafNama) ?></td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">No. Staf</td>
                        <td style="padding:8px 0;font-size:15px;color:#374151;"><?= e($stafNopek) ?></td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">Tahun</td>
                        <td style="padding:8px 0;font-size:15px;color:#374151;font-weight:600;"><?= e($tahun) ?></td>
                      </tr>
                      <?php if ($jabatanNama): ?>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">Jabatan</td>
                        <td style="padding:8px 0;font-size:15px;color:#374151;"><?= e($jabatanNama) ?></td>
                      </tr>
                      <?php endif; ?>
                      <?php if ($bulanGajiTxt): ?>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">Bulan Gaji</td>
                        <td style="padding:8px 0;font-size:15px;color:#374151;"><?= e($bulanGajiTxt) ?></td>
                      </tr>
                      <?php endif; ?>
                      <?php if ($markPPP !== '' || $markPPK !== '' || $purata !== ''): ?>
                      <tr><td colspan="2" style="padding:12px 0 4px 0;border-top:1px solid #e5e7eb;"></td></tr>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">Markah PPP</td>
                        <td style="padding:8px 0;font-size:15px;color:#374151;"><?= e($markPPP) ?></td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">Markah PPK</td>
                        <td style="padding:8px 0;font-size:15px;color:#374151;"><?= e($markPPK) ?></td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">Purata</td>
                        <td style="padding:8px 0;font-size:15px;color:#16a34a;font-weight:600;"><?= e($purata) ?></td>
                      </tr>
                      <?php endif; ?>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Call to Action -->
              <?php if ($reviewUrl): ?>
              <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;margin:0 0 24px 0;">
                <tr>
                  <td style="text-align:center;padding:20px;background:#f0f9ff;border-radius:12px;border:1px solid #bae6fd;">
                    <p style="margin:0 0 16px 0;font-size:15px;font-weight:600;color:#0369a1;">
                      <span style="font-size:18px;vertical-align:middle;margin-right:6px;">&#128269;</span>Semakan Lanjut
                    </p>
                    <!-- Email-compatible button using table -->
                    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 auto;">
                      <tr>
                        <td align="center" style="background:#16a34a;border-radius:10px;padding:0;">
                          <a href="<?= e($reviewUrl) ?>" style="display:block;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;line-height:1.5;padding:14px 32px;border-radius:10px;">
                            Buka Rekod Penilaian
                          </a>
                        </td>
                      </tr>
                    </table>
                    <p style="margin:16px 0 0 0;font-size:13px;color:#0369a1;">
                      Sila klik butang di atas untuk mengakses rekod penilaian
                    </p>
                  </td>
                </tr>
              </table>
              <?php endif; ?>

              <!-- Closing -->
              <p style="margin:0 0 8px 0;font-size:15px;line-height:1.6;color:#374151;">
                Terima kasih diucapkan kepada Yang Berhormat/Yang Berbahagia atas kerjasama dalam melengkapkan penilaian SKT ini.
              </p>
              <p style="margin:16px 0 6px 0;font-size:15px;line-height:1.6;">
                Sekian, terima kasih.
              </p>
              <p style="margin:0;color:#6b7280;font-size:14px;">
                Yang benar,<br>
                <strong style="color:#111827;">Sistem <?= e($systemName) ?></strong>
              </p>
            </td>
          </tr>
          
          <!-- Footer -->
          <tr>
            <td style="padding:20px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;">
              <p style="margin:0;color:#6b7280;font-size:12px;line-height:1.6;text-align:center;">
                <?= nl2br(e($footerNote)) ?>
              </p>
              <div style="margin-top:12px;text-align:center;color:#9ca3af;font-size:11px;">
                Ref: ACK-<?= e($tahun) ?>-<?= e(substr(md5($stafNopek.$tahun.$submittedAt), 0, 6)) ?>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
