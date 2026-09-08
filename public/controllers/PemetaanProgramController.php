<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/PemetaanProgram.php';

class PemetaanProgramController {
    private PemetaanProgram $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private PDO $pdoStaff;

    public function __construct() {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->pdoSPK = Database::pdoMysql(); 

        $this->pdoStudent = Database::pdoSybaseStudent();
        $this->pdoStaff = Database::pdoSybaseStaff();

        $this->model = new PemetaanProgram($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
    } 

    public function handlePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['selectPengajian'])) $_SESSION['peng_laporanprog_kp'] = $_POST['selectPengajian'];
            if (isset($_POST['selectSesi'])) $_SESSION['sesi_laporanprog_kp'] = $_POST['selectSesi'];
            if (isset($_POST['selectProgram'])) $_SESSION['prog_laporanprog_kp'] = $_POST['selectProgram'];
            header('Location: index.php');
            exit;
        }
    }

    public function getHalamanData() {
        $pengajian = $_SESSION['peng_laporanprog_kp'] ?? '';
        $sesi      = $_SESSION['sesi_laporanprog_kp'] ?? '';
        $program   = $_SESSION['prog_laporanprog_kp'] ?? '';
        $stafID    = $_SESSION['f_stafID'] ?? '';
        $ptj       = $this->model->getKodJabatanStaf($stafID); 

        $progName = '-';
        $progList = $this->model->getProgramList($pengajian, $ptj);
        foreach ($progList as $p) {
            if ($p['id_program'] == $program) { $progName = $p['program']; break; }
        }

        return [
            'pengajian'   => $pengajian,
            'sesi'        => $sesi,
            'program'     => $program,
            'programName' => $progName,
            'termList'    => $this->model->getTermList($pengajian),
            'programList' => $progList,
            'peoStats'    => $this->model->getPeoStats($sesi, $program),
            'ploStats'    => $this->model->getPloStats($sesi, $program)
        ];
    }
}
?>