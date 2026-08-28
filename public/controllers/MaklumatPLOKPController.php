<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatPLOKP.php';

class MaklumatPLOKPController
{
    private MaklumatPLOKP $model;
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

        $this->model = new MaklumatPLOKP($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
        
        $this->handlePostRequest();
    }

    private function handlePostRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST["selectPengajianPLO"])) {
                $_SESSION["pengajianplo"] = $_POST["selectPengajianPLO"];
            }

            if (isset($_POST["selectSesiPLO"])) {
                $_SESSION["sesiplo"] = $_POST["selectSesiPLO"];   
            }       

            if (isset($_POST["selectProgramPLO"])) {
                $_SESSION["programplo"] = $_POST["selectProgramPLO"];   
            }              
            
            if (isset($_POST["selectPengajianPLO"]) || isset($_POST["selectSesiPLO"]) || isset($_POST["selectProgramPLO"])) {
                header('Location: index.php');
                exit();
            }
        }

        // Set default 
        $_SESSION["pengajianplo"] = $_SESSION["pengajianplo"] ?? '';
        $_SESSION["sesiplo"] = $_SESSION["sesiplo"] ?? '';
        $_SESSION["programplo"] = $_SESSION["programplo"] ?? '';
    }

    private function getKodTerm(): string
    {
        $pengajian = $_SESSION["pengajianplo"] ?? '';
        if ($pengajian === "Asasi") {
            return "f005term like 'B%'";
        } else if ($pengajian === "Diploma") {
            return "f005term like 'E%'";
        } else if ($pengajian === "Sarjana Muda") {
            return "f005term like 'A%'";
        }
        return "1=1"; // Default jika tiada pilihan supaya sql tidak ralat
    }

    public function getHalamanData(): array
    {
        $programUniversiti = 'Universiti';
        $sesiKursus = $_SESSION["sesiplo"] ?? '';
        $tahapPengajian = $_SESSION["pengajianplo"] ?? '';        
        $idProgram = $_SESSION["programplo"] ?? '';
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
                'list_plo'                => $this->model->getPloList($sesiKursus, $idProgram),
                'list_peo'                => $this->model->getPeoListByProgram($sesiKursus, $idProgram),
                'list_mqf'                => $this->model->getMqfList(),
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'kodJabatan_staf' => '',
                'list_sesi' => [], 'list_program' => [], 'selected_term' => [],
                'selected_program' => [], 'list_plo' => [], 'listPeo' => [],
                'list_mqf' => []
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