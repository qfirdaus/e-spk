<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatPLO.php';

class MaklumatPLOController
{
    private MaklumatPLO $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    private string $errorMessage = '';

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
        $pdoStudent = Database::pdoSybaseStudent();
        if (!$pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }

        $lang = $_SESSION['lang'] ?? 'ms';
        $this->pdoSPK = Database::pdoMysql();
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new MaklumatPLO($this->pdoSPK, $pdoStudent);   
    }

    public function getAllDataPLO(): array
    {
        try {
            return $this->model->getListDataPLO();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }   
    }

    // ######## LOOKUP ###########
    /** Get lookup data */
    public function getAllLookup(): array
    {
        return [
            'list_sesikemasukan' => $this->getLookupSesiKemasukan()
        ];
    }    
    
    public function getLookupSesiKemasukan(): array
    {
        try {
            return $this->model->getSesiKemasukanLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }   


    // // ########## SUBMIT FORM ##########
    // public function submitApplicationDate($userID, $formData)
    // {
    //     try {
    //         $isSaved = $this->model->saveDateApply($userID, $formData);

    //         if ($isSaved) {
    //             return [
    //                 'status' => 'success',
    //                 'message' => 'Maklumat akaun berjaya disimpan'
    //             ];
    //         } else {
    //             return [
    //                 'status' => 'error',
    //                 'message' => 'Gagal mengemaskini maklumat ke dalam pangkalan data.'
    //             ];
    //         }

    //     } catch (Exception $e) {
    //         return [
    //             'status' => 'error',
    //             'message' => 'Ralat Sistem: ' . $e->getMessage()
    //         ];
    //     }        
    // }

    // public function updateDateAppDraft()
    // {
    //     header('Content-Type: application/json; charset=utf-8');

    //     $userID = trim((string)($_SESSION['f_stafID'] ?? ''));

    //     $configType = trim((string)($_POST['update_config_type'] ?? ''));
    //     $sessionCategoryAward = trim((string)($_POST['update_config_category_award'] ?? ''));
    //     $sessionName = trim((string)($_POST['update_config_name_session'] ?? ''));
    //     $configTarikhMula = trim($_POST['update_config_tarikh_mula'] ?? ''); 
    //     $configTarikhTamat = trim($_POST['update_config_tarikh_tamat'] ?? ''); 
    //     $session_status = (int)($_POST['update_config_is_active'] ?? 0);
    //     $record_id = (int)($_POST['update_config_id'] ?? 0);

    //     if ($sessionName === '' || $sessionCategoryAward === '' || $sessionName === '' || $configTarikhMula === '' || $configTarikhTamat === '' ) {
    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => 'Sila lengkapkan semua maklumat tarikh permohonan',
    //         ]);
    //         exit;
    //     }

    //     $newRow = [
    //         'config_type' => $configType,
    //         'config_category_award' => $sessionCategoryAward,
    //         'config_name' => $sessionName,
    //         'start_date' => $configTarikhMula,
    //         'end_date' => $configTarikhTamat,
    //         'is_active' => $session_status,
    //         'updated_by' => $userID
    //     ];

    //     try {
    //         $this->model->updateDateApply($record_id, $newRow);

    //         echo json_encode([
    //             'status' => 'ok',
    //             'message' => 'Rekod berjaya ditambah',
    //             'data' => $newRow,
    //             'id' => $record_id
    //         ]);
    //         exit;

    //     } catch (Exception $e) {

    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => $e->getMessage()
    //         ]);
    //         exit;
    //     }
    // }

    // public function deleteDateAppDraft()
    // {
    //     header('Content-Type: application/json; charset=utf-8');

    //     $userID = trim((string)($_SESSION['f_stafID'] ?? ''));

    //     $id = trim($_POST['id'] ?? '');

    //     if ($id === '') {

    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => 'ID tidak sah'
    //         ]);

    //         exit;
    //     }

    //     try {
    //         $this->model->deleteDateApply($id);

    //         echo json_encode([
    //             'status' => 'ok',
    //             'message' => 'Rekod berjaya ditambah'
    //         ]);
    //         exit;

    //     } catch (Exception $e) {

    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => $e->getMessage()
    //         ]);
    //         exit;
    //     }
    // }    

}