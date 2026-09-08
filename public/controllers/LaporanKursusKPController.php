<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/LaporanKursusKP.php';

class LaporanKursusKPController {
    private LaporanKursusKP $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private PDO $pdoStaff;

    public function __construct() {        
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    
        $this->pdoSPK = Database::pdoMysql(); 
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoStudent = Database::pdoSybaseStudent();
        $this->pdoStaff = Database::pdoSybaseStaff();

        $this->model = new LaporanKursusKP($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
    } 

    public function handlePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['selectKategori'])) $_SESSION['kategorikursus_kp'] = $_POST['selectKategori'];
            if (isset($_POST['selectPengajian'])) $_SESSION['pengajiankursus_kp'] = $_POST['selectPengajian'];
            if (isset($_POST['selectSesi'])) $_SESSION['sesikursus_kp'] = $_POST['selectSesi'];
            
            header('Location: index.php');
            exit;
        }
    }

    public function getHalamanData() {
        $kategori  = $_SESSION['kategorikursus_kp'] ?? '';
        $pengajian = $_SESSION['pengajiankursus_kp'] ?? '';
        $sesi      = $_SESSION['sesikursus_kp'] ?? '';

        return [
            'kategori'    => $kategori,
            'pengajian'   => $pengajian,
            'sesi'        => $sesi,
            'termList'    => $this->model->getTermList($pengajian),
            'courseList'  => $this->model->getCourseList($sesi, $kategori)
        ];
    }

    public function getExcelReportData(string $idKursus): ?array {
        return $this->model->getExcelData($idKursus);
    }    
}
?>