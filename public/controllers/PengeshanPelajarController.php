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
            'wakil' => $this->getLookupWakil()
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
