<?php
// Template: reminder.html.php
// Vars expected:
// $targetNama, $stafNama, $stafNopek, $tahun
// (optional) $jabatanNama, $deadline, $actionUrl, $systemName, $footerNote

if (!function_exists('e')) {
  function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$systemName  = $systemName  ?? 'e-Prestasi';
$title       = "Peringatan Penilaian SKT " . e($tahun);
$targetNama  = $targetNama  ?? '';
$stafNama    = $stafNama    ?? '';
$stafNopek   = $stafNopek   ?? '';
$jabatanNama = $jabatanNama ?? '';
$deadline    = $deadline    ?? ''; // contoh: "31 Ogos 2025"
$actionUrl   = $actionUrl   ?? ''; // link terus ke halaman penilaian
$footerNote  = $footerNote  ?? 'Emel ini dijana secara automatik. Sila jangan balas emel ini.';
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
            <td style="background:#0d6efd;color:#fff;padding:24px 28px;text-align:center;">
              <div style="font-size:24px;font-weight:700;margin-bottom:4px;letter-spacing:-0.5px;">
                <span style="font-size:28px;vertical-align:middle;margin-right:8px;">&#9888;</span>PERINGATAN PENILAIAN SKT
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
                Adalah dimaklumkan bahawa pihak Yang Berhormat/Yang Berbahagia perlu melengkapkan penilaian 
                <strong>Sasaran Kerja Tahunan (SKT)</strong> bagi staf yang berada di bawah seliaan Yang Berhormat/Yang Berbahagia. 
                Kerjasama Yang Berhormat/Yang Berbahagia dalam memastikan penilaian ini dilengkapkan adalah amat dihargai.
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
                      <?php if ($deadline): ?>
                      <tr>
                        <td style="padding:8px 0;color:#6b7280;font-size:14px;vertical-align:top;">Tarikh Akhir</td>
                        <td style="padding:8px 0;font-size:15px;color:#dc2626;font-weight:600;">
                          <span style="font-size:16px;vertical-align:middle;margin-right:4px;">&#9200;</span><?= e($deadline) ?>
                        </td>
                      </tr>
                      <?php endif; ?>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Call to Action -->
              <?php if ($actionUrl): ?>
              <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;margin:0 0 24px 0;">
                <tr>
                  <td style="text-align:center;padding:20px;background:#fef3c7;border-radius:12px;border:1px solid #fbbf24;">
                    <p style="margin:0 0 16px 0;font-size:15px;font-weight:600;color:#92400e;">
                      <span style="font-size:18px;vertical-align:middle;margin-right:6px;">&#9654;</span>Tindakan Segera Diperlukan
                    </p>
                    <!-- Email-compatible button using table (best practice for email) -->
                    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 auto;">
                      <tr>
                        <td align="center" style="background:#0d6efd;border-radius:10px;padding:0;">
                          <a href="<?= e($actionUrl) ?>" style="display:block;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;line-height:1.5;padding:14px 32px;border-radius:10px;">
                            Akses Halaman Penilaian
                          </a>
                        </td>
                      </tr>
                    </table>
                    <p style="margin:16px 0 0 0;font-size:13px;color:#92400e;">
                      Sila klik butang di atas untuk mengakses halaman penilaian
                    </p>
                  </td>
                </tr>
              </table>
              <?php else: ?>
              <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;margin:0 0 24px 0;">
                <tr>
                  <td style="text-align:center;padding:20px;background:#f0f9ff;border-radius:12px;border:1px solid #bae6fd;">
                    <p style="margin:0;font-size:15px;color:#0369a1;font-weight:500;">
                      Sila log masuk ke sistem <strong><?= e($systemName) ?></strong> untuk melengkapkan penilaian SKT yang diperlukan.
                    </p>
                  </td>
                </tr>
              </table>
              <?php endif; ?>

              <!-- Closing -->
              <p style="margin:0 0 8px 0;font-size:15px;line-height:1.6;color:#374151;">
                Kerjasama Yang Berhormat/Yang Berbahagia dalam memastikan penilaian SKT dilengkapkan tepat pada masanya amat dihargai.
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
                Ref: REM-<?= e($tahun) ?>-<?= e(substr(md5($stafNopek.$tahun), 0, 6)) ?>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
