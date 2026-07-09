<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/KodMQF.php';

class KodMQFController
{
    private KodMQF $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private string $errorMessage = '';

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    
        $this->pdoStudent = Database::pdoSybaseStudent();
        if (!$this->pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }

        $this->pdoSPK = Database::pdoMysql();
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new KodMQF($this->pdoSPK, $this->pdoStudent);   
    }

    /** Kumpul semua data yang diperlukan oleh halaman utama (View) */
    public function getHalamanData(): array
    {
        try {
            return [
                'list_mqf' => $this->model->getMQFList(),              
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'list_mqf' => []
            ];
        }
    }

    // Save MQF Baharu
    public function saveMQF($matrik, $formData)
    {
        try {          
            $formData['created_by'] = $matrik;

            $isSaved = $this->model->addMQFBaharu($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod berjaya disimpan'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengemaskini maklumat ke dalam pangkalan data.'
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }        
    }    

    // update MQF
    public function updateMQF($matrik, $formData)
    {
        try {          
            $formData['updated_by'] = $matrik;

            $isSaved = $this->model->updateDataMQF($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod berjaya disimpan'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengemaskini maklumat ke dalam pangkalan data.'
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }        
    }  

    //delete MQF
    public function deleteMQF($matrik, $formData)
    {
        try {          
            $formData['deleted_by'] = $matrik;

            $isSaved = $this->model->deleteDataMQF($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod berjaya dihapus'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengemaskini maklumat ke dalam pangkalan data.'
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }        
    }             

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}