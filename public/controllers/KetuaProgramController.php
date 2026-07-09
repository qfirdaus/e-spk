<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/KetuaProgram.php';

class KetuaProgramController
{
    private KetuaProgram $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private PDO $pdoStaff;
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

        $this->pdoStaff = Database::pdoSybaseStaff();
        if (!$this->pdoStaff instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Staf tidak tersedia.');
        }

        $this->pdoSPK = Database::pdoMysql();
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new KetuaProgram($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
    }

    public function searchStaf($term) 
    {
        try {          
            $senaraiStaf = $this->model->stafList($term);

            if (!empty($senaraiStaf)) {
                return [
                    'status'  => 'success',
                    'message' => 'Rekod carian ditemui.',
                    'data'    => $senaraiStaf // Hantar data ini ke view nanti
                ];
            } else {
                return [
                    'status'  => 'error',
                    'message' => 'Tiada rekod ditemui.',
                    'data'    => []
                ];
            }

        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }     
    }

    /** Kumpul semua data yang diperlukan oleh halaman utama (View) */
    public function getHalamanData(): array
    {
        try {
            return [
                'list_ketua_program' => $this->model->getKetuaProgramList(),              
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'list_ketua_program' => []
            ];
        }
    }

    // Save Ketua Program Baharu
    public function saveHeadProgramme($current_stafID, $formData)
    {
        try {          
            $formData['created_by'] = $current_stafID;

            $isSaved = $this->model->addHeadProgrammeBaharu($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod Ketua Program berjaya disimpan dan akaun pengguna telah diaktifkan.'
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

    //delete Ketua Program
    public function deleteHeadProgramme($current_stafID, $formData)
    {
        try {          
            $formData['updated_by'] = $current_stafID;

            $isSaved = $this->model->deleteDataHeadProgramme($formData);

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