<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/LaporanPLOPK.php';

class LaporanPLOPKController {
    private LaporanPLOPK $model;
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

        $this->model = new LaporanPLOPK($this->pdoSPK, $this->pdoStudent, $this->pdoStaff); 
    } 

    public function handlePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['selectPengajian'])) $_SESSION['pengajianlaporan'] = $_POST['selectPengajian'];
            if (isset($_POST['selectSesi'])) $_SESSION['sesilaporan'] = $_POST['selectSesi'];
            if (isset($_POST['selectProgram'])) $_SESSION['programlaporan'] = $_POST['selectProgram'];
            
            // Redirect untuk elak form resubmission
            header('Location: index.php');
            exit;
        }
    }

    public function getHalamanData() {
        $pengajian = $_SESSION['pengajianlaporan'] ?? '';
        $sesi      = $_SESSION['sesilaporan'] ?? '';
        $program   = $_SESSION['programlaporan'] ?? '';
        $stafID    = $_SESSION['f_stafID'] ?? '';
        $ptj       = $this->model->getKodJabatanStaf($stafID);

        return [
            'pengajian'   => $pengajian,
            'sesi'        => $sesi,
            'program'     => $program,
            'ptj'         => $ptj,
            'termList'    => $this->model->getTermList($pengajian),
            'programList' => $this->model->getProgramList($pengajian, $ptj),
            'ploList'     => $this->model->getPLOList($sesi, $program)
        ];
    }
}