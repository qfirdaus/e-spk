<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/PemetaanKursusPLO.php';

class PemetaanKursusPLOController {
    private PemetaanKursusPLO $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private PDO $pdoStaff;

    public function __construct() {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
        $this->pdoSPK = Database::pdoMysql(); 
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdoStudent = Database::pdoSybaseStudent();
        $this->pdoStaff = Database::pdoSybaseStaff();

        $this->model = new PemetaanKursusPLO($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
    } 

    public function handlePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['selectPengajian'])) $_SESSION['pengajian_map_kp'] = $_POST['selectPengajian'];
            if (isset($_POST['selectSesi'])) $_SESSION['sesi_map_kp'] = $_POST['selectSesi'];
            if (isset($_POST['selectProgram'])) $_SESSION['program_map_kp'] = $_POST['selectProgram'];
            
            header('Location: index.php');
            exit;
        }
    }

    public function getHalamanData() {
        $pengajian = $_SESSION['pengajian_map_kp'] ?? '';
        $sesi      = $_SESSION['sesi_map_kp'] ?? '';
        $program   = $_SESSION['program_map_kp'] ?? '';
        $stafID    = $_SESSION['f_stafID'] ?? '';
        $ptj       = $this->model->getKodJabatanStaf($stafID);     

        return [
            'pengajian'   => $pengajian,
            'sesi'        => $sesi,
            'program'     => $program,
            'termList'    => $this->model->getTermList($pengajian),
            'programList' => $this->model->getProgramList($pengajian, $ptj),
            'ploHeaders'  => $this->model->getPLOList($sesi, $program),
            'courseData'  => $this->model->getPemetaanData($sesi, $program, $ptj)
        ];
    }
}
?>