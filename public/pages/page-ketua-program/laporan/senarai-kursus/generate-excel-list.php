<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../controllers/LaporanKursusKPController.php';

$controller = new LaporanKursusKPController();
$data = $controller->getHalamanData();

$courseList = $data['courseList'] ?? [];
$kategori   = $data['kategori'] ?: 'Semua Kategori';

$sesiKod = $data['sesi'] ?? '';
$sesiPenuh = 'Semua Sesi';

if (!empty($sesiKod) && !empty($data['termList'])) {
    foreach ($data['termList'] as $term) {
        if ((string)$term['f005term'] === (string)$sesiKod) {
            $sesiPenuh = $term['f005term'] . ' - ' . $term['semester'];
            break;
        }
    }
}

// Setup nama fail
$filename = "Senarai_Kursus_" . date('Ymd_His') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>

<html xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head>
        <meta charset="utf-8">
        <style>
            table { 
                border-collapse: collapse; 
                width: 100%; 
                font-family: 'Calibri', Arial, sans-serif; /* Font standard Excel */
                font-size: 11pt; 
            }
            td, th {
                border: 1px solid #000000;
                vertical-align: middle; /* Selaraskan teks ke tengah-tengah kotak */
                padding: 5px;
            }
            .header-title {
                font-size: 16pt;
                font-weight: bold;
                text-align: left;
                border: none;
            }
            .header-subtitle {
                font-size: 11pt;
                font-weight: normal;
                text-align: left;
                color: #595959; /* Kelabu gelap untuk sub-tajuk */
                border: none;
            }
            .table-header th {
                background-color: #0052cc; /* Biru korporat yang cantik */
                color: #ffffff;
                font-weight: bold;
                text-align: center;
                height: 35px;
            }
            .text-center { text-align: center; }
            .text-left { text-align: left; }
            .text-wrap { white-space: normal; word-wrap: break-word; } /* Benarkan teks turun ke bawah jika panjang */
            .font-bold { font-weight: bold; }
        </style>
    </head>
    <body>
        <table>
            <!-- Bahagian Tajuk (Disusun guna row Excel supaya tak terganggu) -->
            <tr>
                <td colspan="6" class="header-title">Laporan Senarai Kursus</td>
            </tr>
            <tr>
                <td colspan="6" class="header-subtitle">
                    Sesi: <?= htmlspecialchars($sesiPenuh) ?> | Kategori: <?= htmlspecialchars($kategori) ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: none;"></td> <!-- Selang satu baris kosong -->
            </tr>

            <!-- Header Jadual -->
            <tr class="table-header">
                <th width="50">Bil.</th>
                <th width="100">Kod Kursus</th>
                <th width="300">Nama Kursus</th>
                <th width="350">Penyelaras</th>
                <th width="100">Kategori</th>
                <th width="130">Tarikh Kemaskini</th>
            </tr>

            <!-- Body Jadual -->
            <tbody>
                <?php if (empty($courseList)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="height: 30px;">Tiada rekod dijumpai.</td>
                    </tr>
                <?php else: ?>
                    <?php $bil = 1; foreach ($courseList as $kursus): ?>
                        <tr>
                            <td class="text-center"><?= $bil++ ?></td>
                            <td class="text-center font-bold"><?= htmlspecialchars($kursus["kod_kursus"] ?? '') ?></td>
                            <td class="text-left text-wrap"><?= htmlspecialchars($kursus["subjekbm"] ?? '') ?></td>
                            <td class="text-left text-wrap">
                                <?= htmlspecialchars($kursus["gelar_nama"] ?? '') ?> - <?= htmlspecialchars($kursus["penyelaras_kursus"] ?? '') ?>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($kursus["kategori_kursus"] ?? '-') ?></td>
                            <td class="text-center">
                                <?= !empty($kursus["updated_date"]) ? date('d-m-Y', strtotime($kursus["updated_date"])) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
</html>