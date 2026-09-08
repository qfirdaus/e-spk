<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/PemetaanKursusUniversitiPLO.php';

class PemetaanKursusUniversitiPLOController {
    private PemetaanKursusUniversitiPLO $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct() {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
        $this->pdoSPK = Database::pdoMysql(); 
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoStudent = Database::pdoSybaseStudent();

        $this->model = new PemetaanKursusUniversitiPLO($this->pdoSPK, $this->pdoStudent);   
    } 

    public function handlePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['selectPengajian'])) $_SESSION['pengajian_map_univ_kp'] = $_POST['selectPengajian'];
            if (isset($_POST['selectSesi'])) $_SESSION['sesi_map_univ_kp'] = $_POST['selectSesi'];
            
            header('Location: index.php');
            exit;
        }
    }

    public function getHalamanData() {
        $pengajian = $_SESSION['pengajian_map_univ_kp'] ?? '';
        $sesi      = $_SESSION['sesi_map_univ_kp'] ?? '';

        return [
            'pengajian'   => $pengajian,
            'sesi'        => $sesi,
            'termList'    => $this->model->getTermList($pengajian),
            'ploHeaders'  => $this->model->getPLOList($sesi),
            'courseData'  => $this->model->getPemetaanData($sesi)
        ];
    }
}
?>