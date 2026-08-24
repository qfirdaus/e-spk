<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatKursusKP.php';

class MaklumatKursusKPController
{
    private MaklumatKursusKP $model;
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
        $this->pdoStaff   = Database::pdoSybaseStaff();
        $this->pdoSPK     = Database::pdoMysql();
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new MaklumatKursusKP($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
        $this->handlePostRequest();
    }

    private function handlePostRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST["selectPengajian"])) {
                $_SESSION["pengajiankursus"] = $_POST["selectPengajian"];
            }

            if (isset($_POST["selectSesi"])) {
                $_SESSION["sesikursus"] = $_POST["selectSesi"];   
                $stafID = $_SESSION['f_stafID'] ?? '';
                $ptj    = $this->model->getKodJabatanStaf($stafID);
                $this->model->syncOfferKursusToSPK($_SESSION["sesikursus"], $ptj, $stafID);
            }       

            if (isset($_POST["selectProgram"])) {
                $_SESSION["programkursus"] = $_POST["selectProgram"];   
            }              
            
            if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi"]) || isset($_POST["selectProgram"])) {
                header('Location: index.php');
                exit();
            }
        }

        $_SESSION["pengajiankursus"] = $_SESSION["pengajiankursus"] ?? '';
        $_SESSION["sesikursus"]      = $_SESSION["sesikursus"] ?? '';
        $_SESSION["programkursus"]   = $_SESSION["programkursus"] ?? '';
    }

    private function getKodTerm(): string
    {
        $pengajian = $_SESSION["pengajiankursus"] ?? '';
        if ($pengajian === "Asasi") {
            return "f005term LIKE 'B%'";
        } else if ($pengajian === "Diploma") {
            return "f005term LIKE 'E%'";
        } else if ($pengajian === "Sarjana Muda") {
            return "f005term LIKE 'A%'";
        }
        return "1=1";
    }

    public function getHalamanData(): array
    {
        $sesiKursus     = $_SESSION["sesikursus"] ?? '';
        $tahapPengajian = $_SESSION["pengajiankursus"] ?? '';        
        $idProgram      = $_SESSION["programkursus"] ?? '';
        $kodTerm        = $this->getKodTerm();
        $stafID         = $_SESSION['f_stafID'] ?? '';
        $ptj            = $this->model->getKodJabatanStaf($stafID);

        try {
            return [
                'kodJabatan_staf'    => $ptj,
                'list_sesi'          => $this->model->getSesiList($kodTerm),      
                'list_program'       => $this->model->getProgramList($tahapPengajian, $ptj),          
                'selected_term'      => $this->model->getSelectedTermDetail($sesiKursus),
                'selected_program'   => $this->model->getSelectedProgramDetail($idProgram),
                'list_kursus'        => $this->model->getKursusList($sesiKursus, $ptj, $idProgram),
                'list_offer_kursus'  => $this->model->getOfferKursusDropdown($sesiKursus, $ptj),
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'kodJabatan_staf' => '', 'list_sesi' => [], 'list_program' => [], 
                'selected_term' => [], 'selected_program' => [], 'list_kursus' => [], 
                'list_offer_kursus' => []
            ];
        }
    }    

    public function getDynamicPensyarahList(string $kodKursus): array
    {
        $sesiKursus = $_SESSION["sesikursus"] ?? '';
        return $this->model->getPensyarahOptions($kodKursus, $sesiKursus);
    }

    public function saveKursus($stafID, $formData)
    {
        try {
            $ptj = $this->model->getKodJabatanStaf($stafID);     
            $formData['ptj']        = $ptj;       
            $formData['created_by'] = $stafID;

            if ($this->model->addKursus($formData)) {
                return ['status' => 'success', 'message' => 'Kursus baharu berjaya ditambah.'];
            }
            return ['status' => 'error', 'message' => 'Gagal menambah kursus ke pangkalan data.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }        
    }

    public function updateKursus($stafID, $formData) 
    {
        try {
            $ptj = $this->model->getKodJabatanStaf($stafID);
            
            // 1. Reset Penyelaras
            if (isset($formData['action']) && $formData['action'] === 'reset_penyelaras') {
                $idKursus = (int)$formData['id_kursus'];
                if ($this->model->resetPenyelaras($idKursus, $stafID)) {
                    return ['status' => 'success', 'message' => 'Penyelaras berjaya dikemas kini (reset).'];
                }
            }

            // 2. Kemaskini Kategori
            if (isset($formData['kategori_kursus'])) {
                $idKursus = (int)$formData['id_kursus'];
                $kategori = $formData['kategori_kursus'];
                $idProgram = $_SESSION["programkursus"] ?? '';

                if ($this->model->updateKategori($idKursus, $kategori, $idProgram, $stafID)) {
                    return ['status' => 'success', 'message' => 'Kategori kursus berjaya dikemas kini.'];
                }
            }

            // 3. Kemaskini Penyelaras
            if (isset($formData['penyelaras_kursus'])) {
                $idKursus    = (int)$formData['id_kursus'];
                $penyelaras = $formData['penyelaras_kursus'];

                if ($this->model->updatePenyelaras($idKursus, $penyelaras, $ptj, $stafID)) {
                    return ['status' => 'success', 'message' => 'Penyelaras kursus berjaya disimpan.'];
                }
            }

            return ['status' => 'error', 'message' => 'Gagal mengemas kini maklumat.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }         
    }   

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}