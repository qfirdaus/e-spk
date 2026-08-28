<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatSLT.php';

class MaklumatSLTController {
    
    private MaklumatSLT $model;
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

        $this->model = new MaklumatSLT($this->pdoSPK, $this->pdoStudent, $this->pdoStaff);   
        $this->handlePostRequest();
    }    

    private function handlePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            $stafId = $_SESSION['id_staf'] ?? '';

            if ($action === 'tambah' || $action === 'kemaskini') {
                $lec = (float)($_POST['txtlecture'] ?? 0);
                $tut = (float)($_POST['txttutorial'] ?? 0);
                $prac = (float)($_POST['txtpractical'] ?? 0);
                $oth = (float)($_POST['txtothers'] ?? 0);
                $nfg = (float)($_POST['txtnf2f'] ?? 0);
                $nfi = (float)($_POST['txtindependent'] ?? 0);
                
                $data = [
                    ':content'  => $_POST['txtCCO'] ?? $_POST['txtcontent'],
                    ':idclo'    => $_POST['selectCLO'],
                    ':lec'      => $lec, ':tut' => $tut, ':prac' => $prac, 
                    ':oth'      => $oth, ':nfg' => $nfg, ':nfi' => $nfi,
                    ':slt'      => ($lec + $tut + $prac + $oth + $nfg + $nfi),
                    ':kursusid' => $_POST['txtkursusid']
                ];

                if ($action === 'tambah') {
                    $data[':created_by'] = $stafId;
                    $this->model->tambahSLT($data);
                } else {
                    $data[':updated_by'] = $stafId;
                    $data[':idslt'] = $_POST['txtidslt'];
                    $this->model->kemaskiniSLT($data);
                }
                header('Location: index.php?status=success'); exit;
            }

            if ($action === 'hapus') {
                $this->model->hapusSLT($_POST['sltid'], $stafId);
                header('Location: index.php?status=deleted'); exit;
            }
        }
        
        if (isset($_POST['selectPengajian'])) $_SESSION['pengajiankursus'] = $_POST['selectPengajian'];
        if (isset($_POST['selectSesi'])) $_SESSION['sesikursus'] = $_POST['selectSesi'];
        if (isset($_POST['selectKursus'])) $_SESSION['kodKursus'] = $_POST['selectKursus'];
    }

    public function getHalamanData() {
        $pengajian = $_SESSION['pengajiankursus'] ?? '';
        $sesi = $_SESSION['sesikursus'] ?? '';
        $kursusId = $_SESSION['kodKursus'] ?? '';
        $idStaf = $_SESSION['f_stafID'] ?? '';

        $data = [
            'termList' => $this->model->getTermList($pengajian),
            'courseList' => $this->model->getCourseList($sesi, $idStaf),
            'cloList' => [],
            'sltList' => [],
            'selectedCourse' => null
        ];

        if (!empty($kursusId)) {
            $data['cloList'] = $this->model->getCLOList($kursusId);
            $data['sltList'] = $this->model->getSLTList($kursusId, $sesi);
            
            foreach ($data['courseList'] as $c) {
                if ($c['id_kursus'] == $kursusId) {
                    $data['selectedCourse'] = $c;
                    break;
                }
            }
        }
        return $data;
    }

    public function saveSLT($userId, $input) {
        try {
            
            $lec = (float)($input['txtlecture'] ?? 0);
            $tut = (float)($input['txttutorial'] ?? 0);
            $prac = (float)($input['txtpractical'] ?? 0);
            $oth = (float)($input['txtothers'] ?? 0);
            $nfg = (float)($input['txtnf2f'] ?? 0);
            $nfi = (float)($input['txtindependent'] ?? 0);

            $data = [
                ':content'  => $input['txtCCO'],
                ':idclo'    => $input['selectCLO'],
                ':lec'      => $lec, 
                ':tut'      => $tut, 
                ':prac'     => $prac, 
                ':oth'      => $oth, 
                ':nfg'      => $nfg, 
                ':nfi'      => $nfi,
                ':slt'      => ($lec + $tut + $prac + $oth + $nfg + $nfi),
                ':kursusid' => $input['txtkursusid'],
                ':created_by' => $userId
            ];

            $success = $this->model->tambahSLT($data);

            if ($success) {
                return [
                    'status' => 'success', 
                    'message' => 'Maklumat SLT berjaya ditambah!'
                ];
            } else {
                return [
                    'status' => 'error', 
                    'message' => 'Gagal menyimpan maklumat ke pangkalan data.'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error', 
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }
    }

}
?>