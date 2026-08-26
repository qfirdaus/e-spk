<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatCLO.php';

class MaklumatCLOController
{
    private MaklumatCLO $model;

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->model = new MaklumatCLO(Database::pdoMysql(), Database::pdoSybaseStudent(), Database::pdoSybaseStaff());   
        $this->handleCarianPost();
    }

    private function handleCarianPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST["selectPengajian"])) $_SESSION["pengajianclo"] = $_POST["selectPengajian"];
            if (isset($_POST["selectSesi"]))      $_SESSION["sesiclo"] = $_POST["selectSesi"];
            if (isset($_POST["selectKursus"]))    $_SESSION["kursusclo"] = $_POST["selectKursus"];
            
            if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi"]) || isset($_POST["selectKursus"])) {
                header('Location: index.php');
                exit();
            }
        }
    }

    public function getHalamanData(): array
    {
        $pengajian = $_SESSION["pengajianclo"] ?? '';
        $sesi      = $_SESSION["sesiclo"] ?? '';
        $kursusID  = $_SESSION["kursusclo"] ?? '';
        $stafID    = $_SESSION['f_stafID'] ?? '';

        $kodTerm = "1=1"; // Default
        if ($pengajian === "Asasi") $kodTerm = "f005term like 'B%'";
        elseif ($pengajian === "Diploma") $kodTerm = "f005term like 'E%'";
        elseif ($pengajian === "Sarjana Muda") $kodTerm = "f005term like 'A%'";

        try{
            return [
                'list_sesi'      => $this->model->getSesiList($kodTerm),
                'list_kursus'    => $this->model->getKursusList($sesi, $stafID),
                'list_plo'       => $kursusID ? $this->model->getPloList($sesi, (int)$kursusID) : [],
                'list_kaedah'    => $this->model->getKaedahList(),
                'list_penilaian' => $this->model->getPenilaianList(),
                'list_clo'       => $kursusID ? $this->model->getCLOList($sesi, (int)$kursusID) : [],
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'list_sesi' => [], 'list_kursus' => [], 'list_plo' => [],  
                'list_kaedah' => [], 'list_penilaian' => [], 'list_clo' => [], 
            ];
        }            
    }

    public function saveCLO($stafID, $formData)
    {
        try {
            $ptj = $this->model->getKodJabatanStaf($stafID);     
            $formData['ptj']        = $ptj;       
            $formData['created_by'] = $stafID;

            $plo       = $formData['chkplo'] ?? [];
            $penilaian = $formData['chkpenilaian'] ?? [];
            $kaedah    = $formData['chkkaedah'] ?? [];

            if ($this->model->addCLO($formData, $plo, $penilaian, $kaedah)) {
                return ['status' => 'success', 'message' => 'Maklumat CLO baharu berjaya ditambah.'];
            }
            
            return ['status' => 'error', 'message' => 'Gagal menambah maklumat CLO ke pangkalan data.'];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }        
    }  

    // Update CLO
    public function updateCLO($stafID, $formData)
    {
        try {
            $cloID = (int)($formData['txtidclo'] ?? 0);
            
            $plo       = $formData['chkplo'] ?? [];
            $penilaian = $formData['chkpenilaian'] ?? [];
            $kaedah    = $formData['chkkaedah'] ?? [];

            if ($this->model->updateCLO($cloID, $formData, $plo, $penilaian, $kaedah, $stafID)) {
                return ['status' => 'success', 'message' => 'Maklumat CLO berjaya dikemaskini.'];
            }
            return ['status' => 'error', 'message' => 'Gagal mengemaskini maklumat CLO.'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }

    // Hapus
    public function deleteCLO($stafID, $cloID)
    {
        try {
            if ($this->model->deleteCLO((int)$cloID, $stafID)) {
                return ['status' => 'success', 'message' => 'Maklumat CLO berjaya dihapuskan.'];
            }
            return ['status' => 'error', 'message' => 'Gagal menghapus maklumat CLO.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }    
}