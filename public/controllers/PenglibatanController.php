<?php
// controllers/PenglibatanController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/Penglibatan.php';

class PenglibatanController
{
    private Penglibatan $model;
    private string $errorMessage = '';

    public function __construct()
    {                
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        
        $pdoIStAD = Database::pdoAdditional('dbx_mysql_istaddb', 'production');
        $pdoIStAD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $lang = $_SESSION['lang'] ?? 'ms';
        $pdoEhepa = Database::pdoMysql();
        $pdoEhepa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new Penglibatan($pdoIStAD, $pdoEhepa);

        $action = $_GET['action'] ?? '';

        if ($action === 'updateDraft') {
            $this->updateDraft();
            exit; 
        }

        if ($action === 'deleteDraft') {
            $this->deleteDraft();
            exit;
        }

        if ($action === 'syncIstad') {
            $this->syncIstad();
            exit;
        }

        if ($action === 'updateDokumen') {
            $this->updateDokumen();
            exit;
        }        
    }

    public function getAllPenglibatan(): array
    {
        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';

        $wrapper = getPenglibatanDraft($matrik);

        // FIRST TIME → create from IStAD 
        // pertama kali buka, belum ada draft, baru generate dari IStAD. Lepas tu save sebagai draft utk next time load terus dari draft
        if (!$wrapper['draft_initialized']) {

            $istad = $this->model->getAllKegiatan($matrik);

            $data = [];

            foreach ($istad as $row) {

                $id = $row['id_kegiatan_pelajar'] ?? null;

                $data[] = [
                    'id' => $id ? 'ISTAD_' . $id : uniqid('DRAFT_'),
                    'id_kegiatan_pelajar' => $id,
                    'sumber' => 'IStAD',
                    'nama' => $row['nama'] ?? '',
                    'tarikh' => $row['tarikh'] ?? null,
                    'wakil' => null,
                    'peringkat' => null,
                    'pencapaian' => $row['pencapaian'] ?? 'PESERTA',
                    'is_dirty' => false,
                    'conflict' => false,
                    'source_override' => false
                ];
            }

            saveDraft($matrik, $data);

            return $data;
        }

        //error_log('TOTAL ROWS: ' . count($wrapper['rows']));
        //error_log(print_r($wrapper['rows'], true)); //apache log
        //return array_values($wrapper['rows'] ?? []);
        $rows = array_values($wrapper['rows'] ?? []);

        //sorting supaya IStAD selalu atas walaupun ada tambahan baru
        usort($rows, function ($a, $b) {

            if (($a['sumber'] ?? '') === 'IStAD') {
                return -1;
            }

            if (($b['sumber'] ?? '') === 'IStAD') {
                return 1;
            }

            return 0;
        });        
        return $rows;
    } 

    public function updateDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';
        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $id    = $_POST['id'] ?? '';
        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';

        $wrapper = getPenglibatanDraft($matrik);
        $rows = $wrapper['rows'];

        if (!$id || !$field) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
            exit;
        }

        foreach ($rows as &$row) {
            $isIstad = str_starts_with($row['id'], 'ISTAD_');
            if ($row['id'] === $id) {
                // check if field is editable for IStAD source
                if ($isIstad && !canEditField($row, $field)) {

                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Field ini tidak dibenarkan dikemaskini untuk data IStAD'
                    ]);
                    exit;
                }

                $row[$field] = $value;
                if ($isIstad) {
                    $row['source_override'] = true;
                } else {
                    $row['is_dirty'] = true;
                }

                break;
            }
        }

        saveDraft($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'id' => $id,
            'field' => $field,
            'value' => $value,
            'next_step' => 'sync_to_ehepa_ready'
        ]);

        exit;
    }

    public function updateDokumen()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';

        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));
        $id = trim($_POST['id'] ?? '');

        if ($id === '') {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID tidak sah'
            ]);
            exit;
        }

        if (
            !isset($_FILES['dokumen'])
            || $_FILES['dokumen']['error'] !== UPLOAD_ERR_OK
        ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dokumen gagal dimuat naik'
            ]);
            exit;
        }

        $file = $_FILES['dokumen'];
        $allowed = ['pdf', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Format fail tidak dibenarkan'
            ]);
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Saiz fail maksimum 5MB'
            ]);
            exit;
        }

        $wrapper = getPenglibatanDraft($matrik);
        $rows = $wrapper['rows'] ?? [];
        $path = 'pages/iStar/permohonan/konvo/uploads/penglibatan/';
        $uploadDir = dirname(__DIR__) . '/' . $path;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $newFileName = $matrik . '-' . uniqid('dok_') . '.' . $ext;
        $fullPath = $uploadDir . $newFileName;
        move_uploaded_file($file['tmp_name'], $fullPath);

        foreach ($rows as &$row) {
            if (($row['id'] ?? '') !== $id) {
                continue;
            }

            // hanya Tambahan boleh update
            if (($row['sumber'] ?? '') !== 'Tambahan') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Rekod IStAD tidak boleh dikemaskini'
                ]);
                exit;
            }

            // delete old file
            if (!empty($row['dokumen']['path'])) {
                $oldPath = dirname(__DIR__) . '/' . $row['dokumen']['path'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // update metadata
            $row['dokumen'] = [
                'filename' => $newFileName,
                'path' => $path . $newFileName,
                'uploaded_at' => date('Y-m-d H:i:s')
            ];

            $row['is_dirty'] = true;

            break;
        }

        saveDraft($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Dokumen berjaya dikemaskini',
            'path' => $row['dokumen']['path']
        ]);

        exit;
    }    

    public function addDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';

        header('Content-Type: application/json');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $wrapper = getPenglibatanDraft($matrik);
        $rows = $wrapper['rows'];        

        // validate input
        $nama = trim($_POST['nama_penuh'] ?? '');
        $tarikh = trim($_POST['tarikh'] ?? '');
        $wakil = $_POST['wakil'] ?? '';
        $peringkat = $_POST['peringkat'] ?? '';
        $pencapaian = $_POST['pencapaian'] ?? '';

        if ($nama === '' || $tarikh === '' || $wakil === '' || $peringkat === '' || $pencapaian === '') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sila lengkapkan semua maklumat'
            ]);
            exit;
        }

        //upload file utk sumber tambahan
        if (!isset($_FILES['dokumen-penglibatan']) || $_FILES['dokumen-penglibatan']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dokumen sokongan wajib dimuat naik'
            ]);
            exit;
        }

        $file = $_FILES['dokumen-penglibatan'];

        $allowed = ['pdf', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Format fail tidak dibenarkan. Hanya PDF, JPG, JPEG dibenarkan.'
            ]);
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Saiz fail maksimum 5MB'
            ]);
            exit;
        }

        // folder simpan
        $path = 'pages/iStar/permohonan/konvo/uploads/penglibatan/';
        $uploadDir = dirname(__DIR__) . '/' . $path;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $newFileName = $matrik . '-' . uniqid('dok_') . '.' . $ext;
        $fullPath = $uploadDir . $newFileName;

        move_uploaded_file($file['tmp_name'], $fullPath);

        //  CREATE ROW
        $newRow = [
            'id' => uniqid('new_'),
            'id_kegiatan_pelajar' => null,
            'sumber' => 'Tambahan',

            'nama' => $nama,
            'tarikh' => $tarikh,
            'wakil' => $wakil,
            'peringkat' => $peringkat,
            'pencapaian' => $pencapaian,
            'dokumen' => [
                'filename' => $newFileName,
                'path' => $path . $newFileName,
                'uploaded_at' => date('Y-m-d H:i:s')
            ],

            'is_dirty' => true,
            'conflict' => false
        ];

        $rows[] = $newRow;

        saveDraft($matrik, $rows);
        $lookup = $this->getAllLookup();

        echo json_encode([
            'status' => 'ok',
            'message' => 'Rekod berjaya ditambah',
            'data' => $newRow,
            'lookup' => $lookup
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function deleteDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';

        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $id = trim($_POST['id'] ?? '');

        if ($id === '') {

            echo json_encode([
                'status' => 'error',
                'message' => 'ID tidak sah'
            ]);

            exit;
        }

        $wrapper = getPenglibatanDraft($matrik);

        $rows = $wrapper['rows'] ?? [];

        $filtered = [];

        foreach ($rows as $row) {

            // skip row yg nak delete
            if (($row['id'] ?? '') === $id && ($row['sumber'] ?? '') === 'Tambahan') {
                
                // delete physical file
                if (!empty($row['dokumen']['path'])) {

                    $oldPath = dirname(__DIR__) . '/' . $row['dokumen']['path'];

                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }             

                continue;
            }

            $filtered[] = $row;
        }

        saveDraft($matrik, $filtered);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Rekod berjaya dipadam'
        ]);

        exit;
    }

    public function syncIstad()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';

        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $wrapper = getPenglibatanDraft($matrik);
        $rows = $wrapper['rows'] ?? [];

        // ambil fresh dari IStAD
        $istad = $this->model->getAllKegiatan($matrik);

        $newIstadRows = [];

        foreach ($istad as $row) {

            $id = $row['id_kegiatan_pelajar'] ?? null;

            $newIstadRows[] = [
                'id' => $id ? 'ISTAD_' . $id : uniqid('ISTAD_'),
                'id_kegiatan_pelajar' => $id,
                'sumber' => 'IStAD',

                'nama' => $row['nama'] ?? '',
                'tarikh' => $row['tarikh'] ?? null,

                'wakil' => null,
                'peringkat' => null,
                'pencapaian' => $row['pencapaian'] ?? 'PESERTA',

                'is_dirty' => false,
                'conflict' => false,
                'source_override' => false
            ];
        }

        // KEEP Tambahan, REPLACE ONLY IStAD
        $filtered = array_values(array_filter($rows, function ($r) {
            return ($r['sumber'] ?? '') !== 'IStAD';
        }));

        $merged = array_merge($filtered, $newIstadRows);

        saveDraft($matrik, $merged);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Data IStAD berjaya diselaraskan'
        ]);
        exit;
    }    

    public function getAllJawatanDisandang(): array
    {
        try {
            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

            return $this->model->getAllJawatan($matrik);

        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    /** Get lookup data */
    public function getAllLookup(): array
    {
        return [
            'wakil' => $this->getLookupWakil(),
            'peringkat' => $this->getLookupPeringkat(),
            'pencapaian' => $this->getLookupPencapaian(),
        ];
    }    
    
    public function getLookupWakil(): array
    {
        try {
            return $this->model->getWakilLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }  
    
    public function getLookupPeringkat(): array
    {
        try {
            return $this->model->getPeringkatLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }  

    public function getLookupPencapaian(): array
    {
        try {
            return $this->model->getPencapaianLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }        
    /** Get lookup data */

    public function testConnection()
    {
        return $this->model->testConnection();
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }    
}
