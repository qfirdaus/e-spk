<?php
// controllers/PenngeshanPelajarController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/PengesahanPelajar.php';

class PengesahanPelajarController
{
    private PengesahanPelajar $model;
    private string $errorMessage = '';

    public function __construct()
    {                
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
        $lang = $_SESSION['lang'] ?? 'ms';
        $pdoEhepa = Database::pdoMysql();
        $pdoEhepa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new PengesahanPelajar($pdoEhepa);      
    }

    // ######## LOOKUP ###########
    /** Get lookup data */
    public function getAllLookup(): array
    {
        return [
            'negeri' => $this->getLookupNegeri(),
            'negara' => $this->getLookupNegara()
        ];
    }    
    
    public function getLookupNegeri(): array
    {
        try {
            return $this->model->getNegeriLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }  
    
    public function getLookupNegara(): array
    {
        try {
            return $this->model->getNegaraLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }  
        
    /** Get lookup data */

    public function submitPermohonan($matrik, $draft)
    {
        try {
            $this->model->savePermohonan($matrik, $draft);

            $file = __DIR__ . '/../pages/iCareS/permohonan/pengesahan-pelajar/temp/' . $matrik . '_draft.json';
            if (file_exists($file)) {
                unlink($file);
            }

            return [
                'status' => 'success'
            ];

        } catch (Exception $e) {

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function testConnection()
    {
        return $this->model->testConnection();
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }    
}
