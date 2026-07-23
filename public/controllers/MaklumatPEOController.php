<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatPEO.php';

class MaklumatPEOController
{
    private MaklumatPEO $model;
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

        $this->model = new MaklumatPEO($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
        
        // Jalankan fungsi semakan POST secara automatik jika ada tindakan dibuat
        $this->handlePostRequest();
    }

    /**Carian Pengajian & Pemilihan Sesi, Kategori Kursus, Penyelaras, Reset */
    private function handlePostRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST["selectPengajian"])) {
                $_SESSION["pengajian"] = $_POST["selectPengajian"];
            }

            if (isset($_POST["selectSesi"])) {
                $_SESSION["sesi"] = $_POST["selectSesi"];   
            }       

            if (isset($_POST["selectProgram"])) {
                $_SESSION["program"] = $_POST["selectProgram"];   
            }              
            
            if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi"]) || isset($_POST["selectProgram"])) {
                header('Location: index.php');
                exit();
            }
        }

        // Set default 
        $_SESSION["pengajian"] = $_SESSION["pengajian"] ?? '';
        $_SESSION["sesi"] = $_SESSION["sesi"] ?? '';
        $_SESSION["program"] = $_SESSION["program"] ?? '';
    }

    /**set WHERE berdasarkan Tahap Pengajian*/
    private function getKodTerm(): string
    {
        $pengajian = $_SESSION["pengajian"] ?? '';
        if ($pengajian === "Asasi") {
            return "f005term like 'B%'";
        } else if ($pengajian === "Diploma") {
            return "f005term like 'E%'";
        } else if ($pengajian === "Sarjana Muda") {
            return "f005term like 'A%'";
        }
        return "1=1"; // Default jika tiada pilihan supaya sql tidak ralat
    }

    /** Kumpul semua data yang diperlukan oleh halaman utama (View) */
    public function getHalamanData(): array
    {
        $programUniversiti = 'Universiti';
        $sesiKursus = $_SESSION["sesi"] ?? '';
        $tahapPengajian = $_SESSION["pengajian"] ?? '';        
        $idProgram = $_SESSION["program"] ?? '';
        $kodTerm = $this->getKodTerm();
        $stafID = $_SESSION['f_stafID'] ?? '';
        $ptj = $this->model->getKodJabatanStaf($stafID);

        try {
            return [
                'kodJabatan_staf'         => $ptj,
                'list_sesi'               => $this->model->getSesiList($kodTerm),      
                'list_program'            => $this->model->getProgramList($tahapPengajian, $ptj),          
                'selected_term'           => $this->model->getSelectedTermDetail($sesiKursus, $kodTerm),
                'selected_program'        => $this->model->getSelectedProgramDetail($idProgram),
                'list_peo'                => $this->model->getPeoList($sesiKursus, $idProgram),
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'kodJabatan_staf' => '',
                'list_sesi' => [], 'list_program' => [], 'selected_term' => [],
                'selected_program' => [], 'list_peo' => [],
                // 'list_subject_registered' => [],  'list_subject_all' => []
            ];
        }
    }

    // Save PEO Baharu
    public function savePEO($matrik, $formData)
    {
        try {
            $formData['created_by'] = $matrik;

            $isSaved = $this->model->addPeoBaharu($formData);

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

    // update PEO
    public function updatePEO($matrik, $formData) {
        try {
            $formData['updated_by'] = $matrik;

            $isSaved = $this->model->updateDataPeo($formData);

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

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}