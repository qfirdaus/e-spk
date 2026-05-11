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

        $action = $_GET['action'] ?? '';

        if ($action === 'updateDraft') {
            $this->updateDraft();
            exit; // penting supaya stop execution
        }

        $pdoIStAD = Database::pdoAdditional('dbx_mysql_istaddb', 'production');
        $pdoIStAD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $lang = $_SESSION['lang'] ?? 'ms';
        $pdoEhepa = Database::pdoMysql();
        $pdoEhepa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new Penglibatan($pdoIStAD, $pdoEhepa);
    }

    public function getAllPenglibatan(): array
    {
        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $istad = $this->model->getAllKegiatan($matrik);

        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';

        $draft = getPenglibatanDraft($matrik);
        $map = [];

        /** MAP EXISTING DRAFT  */
        foreach ($draft as &$d) {
            if (!empty($d['id_kegiatan_pelajar'])) {
                $map[$d['id_kegiatan_pelajar']] = &$d;
            }
        }
        unset($d);

        /** SYNC IStAD */
        foreach ($istad as $row) {

            $istadId = $row['id_kegiatan_pelajar'] ?? null;

            if ($istadId && isset($map[$istadId])) {
                if (empty($map[$istadId]['source_override'])){
                    $map[$istadId]['nama'] = $row['nama'] ?? '';
                    $map[$istadId]['tarikh'] = $row['tarikh'] ?? null;
                    $map[$istadId]['pencapaian'] = $row['pencapaian'] ?? 'PESERTA';
                }
            } else {
                $draft[] = [
                    'id' => $istadId ? 'ISTAD_' . $istadId : uniqid('DRAFT_'),
                    'id_kegiatan_pelajar' => $istadId,
                    'sumber' => 'IStAD',
                    'nama' => $row['nama'] ?? '',
                    'tarikh' => $row['tarikh'] ?? null,
                    'wakil' => null,
                    'peringkat' => null,
                    'pencapaian' => $row['pencapaian'] ?? 'PESERTA',
                    'is_dirty' => false,
                    'conflict' => false,
                    'original_snapshot' => [
                        'nama' => $row['nama'] ?? '',
                        'tarikh' => $row['tarikh'] ?? null,
                    ]
                ];
            }
        }

        savePenglibatanDraft($matrik, $draft);
        return $draft;
    }

    public function getPenglibatanDraft(): array
    {
        try {

            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

            $istad = $this->model->getAllKegiatan($matrik);

            return loadPenglibatanDraft($matrik, $istad);

        } catch (Throwable $e) {

            return [];
        }
    }    

    public function updateDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';
        header('Content-Type: application/json; charset=utf-8');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $id    = $_POST['id'] ?? '';
        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';

        $rows = getPenglibatanDraft($matrik);

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
                        'message' => 'Field ini tidak dibenarkan untuk data IStAD'
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

        savePenglibatanDraft($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'id' => $id,
            'field' => $field,
            'value' => $value,
            'next_step' => 'sync_to_ehepa_ready'
        ]);

        exit;
    }

    public function addDraft()
    {
        require_once __DIR__ . '/../pages/iStar/permohonan/konvo/helpers/PenglibatanDraftHelper.php';

        header('Content-Type: application/json');

        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        $rows = getPenglibatanDraft($matrik);

        $newRow = [
            'id' => uniqid('new_'),
            'id_kegiatan_pelajar' => null,
            'sumber' => 'Tambahan',
            'nama' => $_POST['nama_penuh'] ?? '',
            'tarikh' => $_POST['tarikh'] ?? '',
            'wakil' => $_POST['wakil'] ?? '',
            'peringkat' => $_POST['peringkat'] ?? '',
            'pencapaian' => $_POST['pencapaian'] ?? '',
            'is_dirty' => true,
            'conflict' => false
        ];

        $rows[] = $newRow;

        savePenglibatanDraft($matrik, $rows);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Rekod berjaya ditambah',
            'data' => $newRow
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
