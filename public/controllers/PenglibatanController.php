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

    public function getAllPenglibatan(): array
    {
        try {
            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

            return $this->model->getAllKegiatan($matrik);

        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
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
