<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/KlusterHasilPembelajaran.php';

class KlusterHasilPembelajaranController
{
    private KlusterHasilPembelajaran $model;
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

        $this->model = new KlusterHasilPembelajaran($this->pdoSPK, $this->pdoStudent);   
    }

    /** Kumpul semua data yang diperlukan oleh halaman utama (View) */
    public function getHalamanData(): array
    {
        try {
            return [
                'list_loc' => $this->model->getLocList(),              
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'list_loc' => []
            ];
        }
    }

    // Save LOC Baharu
    public function saveLOC($matrik, $formData)
    {
        try {          
            $formData['created_by'] = $matrik;

            $isSaved = $this->model->addLocBaharu($formData);

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

    // update LOC
    public function updateLOC($matrik, $formData)
    {
        try {          
            $formData['updated_by'] = $matrik;

            $isSaved = $this->model->updateDataLOC($formData);

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

    //delete LOC
    public function deleteLOC($matrik, $formData)
    {
        try {          
            $formData['deleted_by'] = $matrik;

            $isSaved = $this->model->deleteDataLOC($formData);

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