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
    }

    // PENGLIBATAN
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

    // ####### JAWATAN DISANDANG ########
    public function getAllJawatanDisandang(): array
    {
        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/JawatanDraftHelper.php';

        $wrapper = getJawatanDraft($matrik);

        if (!$wrapper['draft_initialized']) {

            $istad = $this->model->getAllJawatan($matrik);

            initJawatanDraft($matrik, $istad);

            // reload semula dari file (IMPORTANT)
            $wrapper = getJawatanDraft($matrik);
        }

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

    public function updateJawatanDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/JawatanDraftHelper.php';
        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $id    = trim($_POST['id'] ?? '');
        $field = trim($_POST['field'] ?? '');
        $value = $_POST['value'] ?? '';

        $wrapper = getJawatanDraft($matrik);
        $rows = $wrapper['rows'] ?? [];

        if ($id === '' || $field === '') {

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
                if ($isIstad && !canEditFieldJawatan($row, $field)) {

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

        // save balik
        saveJawatanDraftRows($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'id' => $id,
            'field' => $field,
            'value' => $value,
            'next_step' => 'sync_to_ehepa_ready'
        ]);

        exit;
    }

    public function updateDokumenJawatan()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/JawatanDraftHelper.php';

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

        $wrapper = getJawatanDraft($matrik);
        $rows = $wrapper['rows'] ?? [];
        $path = 'pages/iStar/permohonan/konvo/uploads/jawatan/';
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

        saveJawatanDraftRows($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Dokumen berjaya dikemaskini',
            'path' => $row['dokumen']['path']
        ]);

        exit;
    }   

    //Add New Jawatan Disandang (Tambahan) - with file upload
    public function addJawatanDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/JawatanDraftHelper.php';

        header('Content-Type: application/json');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $wrapper = getJawatanDraft($matrik);
        $rows = $wrapper['rows'];        

        // validate input
        $id_kategori_aktiviti = trim($_POST['id_aktiviti'] ?? '');
        $kod_kategori_aktiviti = trim($_POST['kategori_aktiviti'] ?? '');
        $kategori_aktiviti = trim($_POST['aktiviti_text'] ?? '');
        $nama_bp_program = trim($_POST['nama_bp_program'] ?? '');
        $tarikh_lantikan = trim($_POST['tarikh'] ?? '');
        $id_jawatan = isset($_POST['jawatan']) ? (int) $_POST['jawatan'] : null;
        $jawatan_text = $_POST['jawatan_text'] ?? '';
        $peringkat = $_POST['peringkat'] ?? '';

        if ($kod_kategori_aktiviti === '' || $nama_bp_program === '' || $tarikh_lantikan === '' || $id_jawatan === '' || $peringkat === '') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sila lengkapkan semua maklumat'
            ]);
            exit;
        }

        //upload file utk sumber tambahan
        if (!isset($_FILES['dokumen-jawatan']) || $_FILES['dokumen-jawatan']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dokumen sokongan wajib dimuat naik'
            ]);
            exit;
        }

        $file = $_FILES['dokumen-jawatan'];

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
        $path = 'pages/iStar/permohonan/konvo/uploads/jawatan/';
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
            'id_kegiatan_badan' => null,
            'sumber' => 'Tambahan',
            'id_kategori_aktiviti' => $id_kategori_aktiviti,
            'kod_kategori_aktiviti' => $kod_kategori_aktiviti,
            'kategori_aktiviti' => $kategori_aktiviti,
            'nama_bp_program' => $nama_bp_program,
            'id_jawatan' => $id_jawatan,
            'jawatan' => $jawatan_text,
            'tarikh_lantikan' => $tarikh_lantikan,
            'peringkat' => $peringkat,
            'dokumen' => [
                'filename' => $newFileName,
                'path' => $path . $newFileName,
                'uploaded_at' => date('Y-m-d H:i:s')
            ],

            'is_dirty' => true,
            'conflict' => false
        ];

        $rows[] = $newRow;

        saveJawatanDraftRows($matrik, $rows);
        $lookup = $this->getAllLookup();

        echo json_encode([
            'status' => 'ok',
            'message' => 'Rekod berjaya ditambah',
            'data' => $newRow,
            'lookup' => $lookup
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function deleteJawatanDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/JawatanDraftHelper.php';

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

        $wrapper = getJawatanDraft($matrik);

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

        saveJawatanDraftRows($matrik, $filtered);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Rekod berjaya dipadam'
        ]);

        exit;
    }    

    public function syncIstadJawatan()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/JawatanDraftHelper.php';

        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $wrapper = getJawatanDraft($matrik);
        $rows = $wrapper['rows'] ?? [];

        // ambil fresh dari IStAD
        $istad = $this->model->getAllJawatan($matrik);
        $newIstadRows = [];

         foreach ($istad as $row) {

            $id = $row['id_kegiatan_badan'] ?? null;

            $newIstadRows[] = [
                'id' => $id ? 'ISTAD_' . $id : uniqid('ISTAD_'),
                'id_kegiatan_badan' => $row['id_kegiatan_badan'] ?? null,
                'sumber' => 'IStAD',

                'id_kategori_aktiviti' => $row['id_kategori_aktiviti'] ?? null,
                'kod_kategori_aktiviti' => $row['kod_kategori_aktiviti'] ?? null,
                'kategori_aktiviti' => $row['kategori_aktiviti'] ?? null,

                'nama_bp_program' => $row['nama_bp_program'] ?? '',
                'id_jawatan' => $row['id_jawatan'] ?? '',
                'jawatan' => $row['jawatan'] ?? '',
                'tarikh_lantikan' => $row['tarikh_mula'] ?? '',

                'peringkat' => $row['peringkat'] ?? null,

                'is_dirty' => false,
                'source_override' => false,
                'conflict' => false    
            ];
        }         
     
        // KEEP Tambahan, REPLACE ONLY IStAD
        $filtered = array_values(array_filter($rows, function ($r) {
            return ($r['sumber'] ?? '') !== 'IStAD';
        }));

        $merged = array_merge($filtered, $newIstadRows);

        saveJawatanDraftRows($matrik, $merged);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Data IStAD berjaya diselaraskan'
        ]);
        exit;
    }

    // ####### ANUGERAH ########
    public function getAllAnugerah(): array
    {
        try {
            require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/AnugerahDraftHelper.php';

            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));
            $wrapper = getAnugerahDraft($matrik);

            return array_values($wrapper['rows'] ?? []);
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    public function addAnugerahDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/AnugerahDraftHelper.php';

        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));
        $wrapper = getAnugerahDraft($matrik);
        $rows = $wrapper['rows'] ?? [];

        $namaAnugerah = trim((string)($_POST['nama_anugerah'] ?? ''));
        $tahun = trim((string)($_POST['tahun'] ?? ''));
        $kurniaanPemberian = trim((string)($_POST['kurniaan_pemberian'] ?? ''));
        $peringkat = trim((string)($_POST['peringkat'] ?? ''));

        if ($namaAnugerah === '' || $tahun === '' || $kurniaanPemberian === '' || $peringkat === '') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sila lengkapkan semua maklumat anugerah',
            ]);
            exit;
        }

        if (!isset($_FILES['dokumen-anugerah']) || $_FILES['dokumen-anugerah']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dokumen sokongan anugerah wajib dimuat naik',
            ]);
            exit;
        }

        $file = $_FILES['dokumen-anugerah'];
        $allowed = ['pdf', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Format fail tidak dibenarkan. Hanya PDF, JPG, JPEG dibenarkan.',
            ]);
            exit;
        }

        if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Saiz fail maksimum 5MB',
            ]);
            exit;
        }

        $path = 'pages/iStar/permohonan/konvo/uploads/anugerah/';
        $uploadDir = dirname(__DIR__) . '/' . $path;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $newFileName = $matrik . '-' . uniqid('award_') . '.' . $ext;
        $fullPath = $uploadDir . $newFileName;

        move_uploaded_file($file['tmp_name'], $fullPath);

        $newRow = [
            'id' => uniqid('anugerah_'),
            'nama_anugerah' => $namaAnugerah,
            'tahun' => $tahun,
            'kurniaan_pemberian' => $kurniaanPemberian,
            'peringkat' => $peringkat,
            'dokumen' => [
                'filename' => $newFileName,
                'path' => $path . $newFileName,
                'uploaded_at' => date('Y-m-d H:i:s')
            ],

            'is_dirty' => true,
            'conflict' => false
        ];

        $rows[] = $newRow;

        saveAnugerahDraftRows($matrik, $rows);
        $lookup = $this->getAllLookup();

        echo json_encode([
            'status' => 'ok',
            'message' => 'Rekod berjaya ditambah',
            'data' => $newRow,
            'lookup' => $lookup
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function updateAnugerahDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/AnugerahDraftHelper.php';
        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $id    = trim($_POST['id'] ?? '');
        $field = trim($_POST['field'] ?? '');
        $value = $_POST['value'] ?? '';

        $wrapper = getAnugerahDraft($matrik);
        $rows = $wrapper['rows'] ?? [];

        if ($id === '' || $field === '') {

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
                if ($isIstad && !canEditFieldJawatan($row, $field)) {

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

        // save balik
        saveAnugerahDraftRows($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'id' => $id,
            'field' => $field,
            'value' => $value,
            'next_step' => 'sync_to_ehepa_ready'
        ]);

        exit;
    }

    public function updateDokumenAnugerah()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/AnugerahDraftHelper.php';

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

        $wrapper = getAnugerahDraft($matrik);
        $rows = $wrapper['rows'] ?? [];
        $path = 'pages/iStar/permohonan/konvo/uploads/anugerah/';
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

        saveAnugerahDraftRows($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Dokumen berjaya dikemaskini',
            'path' => $row['dokumen']['path']
        ]);

        exit;
    }      
    
    public function deleteAnugerahDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/AnugerahDraftHelper.php';

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

        $wrapper = getAnugerahDraft($matrik);

        $rows = $wrapper['rows'] ?? [];

        $filtered = [];

        foreach ($rows as $row) {

            // skip row yg nak delete
            if (($row['id'] ?? '') === $id) {
                
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

        saveAnugerahDraftRows($matrik, $filtered);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Rekod berjaya dipadam'
        ]);

        exit;
    }

    // ######## LOOKUP ###########
    /** Get lookup data */
    public function getAllLookup(): array
    {
        return [
            'wakil' => $this->getLookupWakil(),
            'peringkat' => $this->getLookupPeringkat(),
            'pencapaian' => $this->getLookupPencapaian(),
            'jawatan' => $this->getLookupJawatan(),
            'kategori_perjawatan' => $this->getLookupKategoriPerjawatan(),
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

    public function getLookupJawatan(): array
    {
        try {
            return $this->model->getJawatanLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }      
    
    public function getLookupKategoriPerjawatan(): array
    {
        try {
            return $this->model->getKategoriPerjawatanLookup();
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
