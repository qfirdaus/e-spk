<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatKursusProgram.php';

class MaklumatKursusProgramController
{
    private MaklumatKursusProgram $model;
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

        // Pasangkan pdoSPK dan pdoStudent ke dalam Model
        $this->model = new MaklumatKursusProgram($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
        
        // Jalankan fungsi semakan POST secara automatik jika ada carian dibuat
        $this->handlePostRequest();
    }

    /**
     * Mengendalikan form submission (Carian/Search)
     */
    private function handlePostRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST["selectPengajianKursus"])) {
                $_SESSION["pengajiankursus"] = $_POST["selectPengajianKursus"];
            }

            if (isset($_POST["selectSesiKursus"])) {
                $_SESSION["sesikursus"] = $_POST["selectSesiKursus"];   
            }       

            if (isset($_POST["selectProgramKursus"])) {
                $_SESSION["programkursus"] = $_POST["selectProgramKursus"];   
            }              
            
            if (isset($_POST["selectPengajianKursus"]) || isset($_POST["selectSesiKursus"]) || isset($_POST["selectProgramKursus"])) {
                header('Location: index.php');
                exit();
            }
        }

        // Set default 
        $_SESSION["pengajiankursus"] = $_SESSION["pengajiankursus"] ?? '';
        $_SESSION["sesikursus"] = $_SESSION["sesikursus"] ?? '';
        $_SESSION["programkursus"] = $_SESSION["programkursus"] ?? '';
    }

    /**
     * Menjana kod penapisan berdasarkan Tahap Pengajian
     */
    private function getKodTerm(): string
    {
        $pengajian = $_SESSION["pengajiankursus"] ?? '';
        if ($pengajian === "Asasi") {
            return "f005term like 'B%'";
        } else if ($pengajian === "Diploma") {
            return "f005term like 'E%'";
        } else if ($pengajian === "Sarjana Muda") {
            return "f005term like 'A%'";
        }
        return "1=1"; // Default jika tiada pilihan supaya sql tidak ralat
    }

    /**
     * Mengumpul semua data yang diperlukan oleh halaman utama (View)
     */
    public function getHalamanData(): array
    {
        $programUniversiti = 'Program';
        $sesiKursus = $_SESSION["sesikursus"] ?? '';
        $tahapPengajian = $_SESSION["pengajiankursus"] ?? '';        
        $idProgram = $_SESSION["programkursus"] ?? '';
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
                'list_kursus_program'     => $this->model->getKursusProgramList($sesiKursus, $ptj, $idProgram, $programUniversiti),
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'kodJabatan_staf' => '',
                'list_sesi' => [], 'list_program' => [], 'selected_term' => [],
                'selected_program' => [], 'list_plo' => [], 'listPeo' => []
            ];
        }
    }    

    public function savePLO($stafID, $formData)
    {
        try {
            $ptj = $this->model->getKodJabatanStaf($stafID);     
            $formData['ptj'] = $ptj;       
            $formData['created_by'] = $stafID;

            $isSaved = $this->model->addPloBaharu($formData);

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

    public function updatePLO($matrik, $formData) {
        try {
            $formData['updated_by'] = $matrik;

            $isSaved = $this->model->updateDataPlo($formData);

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

    public function deletePLO($stafID, $formData) {
        try {
            $formData['updated_by'] = $stafID;

            $isSaved = $this->model->deleteDataPlo($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod berjaya dihapuskan'
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
    
    public function copyPLO($stafID, $formData)
    {
        try {
            $ptj = $this->model->getKodJabatanStaf($stafID);     
            $formData['ptj'] = $ptj;              
            $formData['created_by'] = $stafID;

            $isCopied = $this->model->salinPloSesi($formData);

            if ($isCopied) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod PLO berjaya disalin ke sesi baharu.'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyalin maklumat PLO ke dalam pangkalan data.'
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