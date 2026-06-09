<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/ConfigiStar.php';

class iStarConfigController
{
    private ConfigiStar $model;
    private PDO $pdoEhepa;

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
        $lang = $_SESSION['lang'] ?? 'ms';
        $this->pdoEhepa = Database::pdoMysql();
        $this->pdoEhepa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new ConfigiStar($this->pdoEhepa);   
    }

    public function getAllDateConfig(): array
    {
        $stafID = trim((string)($_SESSION['f_stafID'] ?? ''));

        return $this->model->getListDateConfig($stafID);    
    }

    public function addDateAppDraft()
    {
        header('Content-Type: application/json; charset=utf-8');

        $userID = trim((string)($_SESSION['f_stafID'] ?? ''));

        $configType = trim((string)($_POST['config_type'] ?? ''));
        $sessionCategoryAward = trim((string)($_POST['config_category_award'] ?? ''));
        $sessionName = trim((string)($_POST['config_name_session'] ?? ''));
        $configTarikhMula = trim($_POST['config_tarikh_mula'] ?? ''); 
        $configTarikhTamat = trim($_POST['config_tarikh_tamat'] ?? ''); 
        $session_status = (int)($_POST['config_is_active'] ?? 0);

        if ($sessionName === '' || $sessionCategoryAward === '' || $sessionName === '' || $configTarikhMula === '' || $configTarikhTamat === '' ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sila lengkapkan semua maklumat tarikh permohonan',
            ]);
            exit;
        }

        $newRow = [
            'config_type' => $configType,
            'config_category_award' => $sessionCategoryAward,
            'config_name' => $sessionName,
            'start_date' => $configTarikhMula,
            'end_date' => $configTarikhTamat,
            'is_active' => $session_status,
            'created_by' => $userID
        ];

        try {
            $this->model->saveDateApply($configType, $sessionName, $newRow);

            echo json_encode([
                'status' => 'ok',
                'message' => 'Rekod berjaya ditambah'
            ]);
            exit;

        } catch (Exception $e) {

            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }    

    public function updateDateAppDraft()
    {
        header('Content-Type: application/json; charset=utf-8');

        $userID = trim((string)($_SESSION['f_stafID'] ?? ''));

        $configType = trim((string)($_POST['update_config_type'] ?? ''));
        $sessionCategoryAward = trim((string)($_POST['update_config_category_award'] ?? ''));
        $sessionName = trim((string)($_POST['update_config_name_session'] ?? ''));
        $configTarikhMula = trim($_POST['update_config_tarikh_mula'] ?? ''); 
        $configTarikhTamat = trim($_POST['update_config_tarikh_tamat'] ?? ''); 
        $session_status = (int)($_POST['update_config_is_active'] ?? 0);
        $record_id = (int)($_POST['update_config_id'] ?? 0);

        if ($sessionName === '' || $sessionCategoryAward === '' || $sessionName === '' || $configTarikhMula === '' || $configTarikhTamat === '' ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sila lengkapkan semua maklumat tarikh permohonan',
            ]);
            exit;
        }

        $newRow = [
            'config_type' => $configType,
            'config_category_award' => $sessionCategoryAward,
            'config_name' => $sessionName,
            'start_date' => $configTarikhMula,
            'end_date' => $configTarikhTamat,
            'is_active' => $session_status,
            'updated_by' => $userID
        ];

        try {
            $this->model->updateDateApply($record_id, $newRow);

            echo json_encode([
                'status' => 'ok',
                'message' => 'Rekod berjaya ditambah',
                'data' => $newRow,
                'id' => $record_id
            ]);
            exit;

        } catch (Exception $e) {

            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    public function deleteDateAppDraft()
    {
        header('Content-Type: application/json; charset=utf-8');

        $userID = trim((string)($_SESSION['f_stafID'] ?? ''));

        $id = trim($_POST['id'] ?? '');

        if ($id === '') {

            echo json_encode([
                'status' => 'error',
                'message' => 'ID tidak sah'
            ]);

            exit;
        }

        try {
            $this->model->deleteDateApply($id);

            echo json_encode([
                'status' => 'ok',
                'message' => 'Rekod berjaya ditambah'
            ]);
            exit;

        } catch (Exception $e) {

            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }    

}