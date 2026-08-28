<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatKursusPK.php';

class MaklumatKursusPKController
{
    private MaklumatKursusPK $model;
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

        $this->model = new MaklumatKursusPK($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
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
            }            
            
            if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi"])) {
                header('Location: index.php?page=maklumat-kursus-pk');
                exit();
            }
        }

        $_SESSION["pengajiankursus"] = $_SESSION["pengajiankursus"] ?? '';
        $_SESSION["sesikursus"]      = $_SESSION["sesikursus"] ?? '';
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
        $pengajian = $_SESSION["pengajiankursus"] ?? '';
        $sesi      = $_SESSION["sesikursus"] ?? '';
        $kodTerm   = $this->getKodTerm();
        $stafID    = $_SESSION['f_stafID'] ?? '';

        try{
            return [
                'list_sesi'   => $this->model->getSesiList($kodTerm),
                'list_kursus' => !empty($sesi) ? $this->model->getKursusList($sesi, $stafID) : []
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'list_sesi' => [], 'list_kursus' => [], 
            ];
        } 
    }

    public function getSenaraiKemahiran(int $kursusId): array
    {
        try {
            return $this->model->getSenaraiKemahiran($kursusId);
        } catch (Exception $e) {
            return []; 
        }
    }

    public function getPenilaianList(int $kursusId, string $term): array
    {
        try {
            return $this->model->getPenilaianList($kursusId, $term);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getRujukanList(int $kursusId, string $term): array
    {
        try {
            return $this->model->getRujukanList($kursusId, $term);
        } catch (Exception $e) {
            return [];
        }
    }    

    public function getExcelData(int $kursusId): array
    {
        $data = [];

        try {
            $courseInfo = $this->model->getCourseExcelInfo($kursusId);
            $data['course'] = $courseInfo;
            
            $kodk = $courseInfo['kod_kursus'] ?? '';
            $courseDetails = [];
            if (!empty($kodk)) {
                $courseDetails = $this->model->getCourseExcelDetails($kodk);
            }
            
            $syarat = trim(($courseDetails['f240psyarat1'] ?? '') . " " . 
                           ($courseDetails['f240psyarat2'] ?? '') . " " . 
                           ($courseDetails['f240psyarat3'] ?? '') . " " . 
                           ($courseDetails['f240psyarat4'] ?? '') . " " . 
                           ($courseDetails['f240psyarat5'] ?? ''));
            $data['prerequisite'] = $syarat;

            // CLO & Mapping (PLO, Teaching Method, Assessment)
            $clos = $this->model->getCourseCLOList($kursusId);
            $cloData = [];
            foreach ($clos as $clo) {
                $cloId = (int)$clo['id_clo'];
                $plos = $this->model->getCloPloMapping($cloId);
                $teachingMethods = $this->model->getCloTeachingMethods($cloId);
                $assessments = $this->model->getCloAssessments($cloId);
                
                $cloData[] = [
                    'kod_clo' => $clo['kod_clo'],
                    'keterangan_bm' => $clo['keterangan_bm'],
                    'plos' => $plos,
                    'teaching_methods' => implode(', ', $teachingMethods),
                    'assessments' => implode(', ', $assessments)
                ];
            }
            $data['clos'] = $cloData;

            $data['skills'] = $this->model->getExcelSkills($kursusId);
            $data['cco'] = $this->model->getExcelCCO($kursusId);
            
            // Penilaian Berterusan (1) & Akhir (2)
            $data['continuous'] = $this->model->getExcelAssessment($kursusId, 1);
            $data['final'] = $this->model->getExcelAssessment($kursusId, 2);
            
            // Rujukan
            $data['references'] = $this->model->getExcelReferences($kursusId);

        } catch (Exception $e) {
            error_log("Ralat Penjanaan Excel MVC: " . $e->getMessage());
        }

        return $data;
    }   

    public function saveUpdateKursus($stafID, $postData)
    {
        try {
            $kemahiranArr = $postData['chkkemahiran'] ?? [];

            $penilaianArr = [];
            $len = (int)($postData['txt_len'] ?? 0);
            for ($i = 0; $i < $len; $i++) {
                $idPenilaian = $postData["txt_idpenilaian{$i}"] ?? '';
                if (!empty($idPenilaian)) {
                    $f2f  = (float)($postData["c_f2f{$i}"] ?? 0);
                    $nf2f = (float)($postData["c_nf2f{$i}"] ?? 0);
                    $penilaianArr[] = [
                        'id_penilaian' => $idPenilaian,
                        'percentage'   => (float)($postData["c_percent{$i}"] ?? 0),
                        'f2f'          => $f2f,
                        'nf2f'         => $nf2f,
                        'slt'          => $f2f + $nf2f 
                    ];
                }
            }

            $rujukanArr = [];
            $count = (int)($postData['count'] ?? 0);
            for ($j = 1; $j <= $count; $j++) {
                $ref = trim($postData["ref{$j}"] ?? '');
                if ($ref !== '') {
                    $rujukanArr[] = $ref;
                }
            }

            if ($this->model->updateKursus($postData, $kemahiranArr, $penilaianArr, $rujukanArr, $stafID)) {
                return ['status' => 'success', 'message' => 'Maklumat Kursus berjaya dikemaskini.'];
            }
            
            return ['status' => 'error', 'message' => 'Gagal mengemaskini maklumat kursus.'];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}