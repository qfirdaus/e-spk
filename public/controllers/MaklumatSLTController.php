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

    public function updateSLT($userId, $input) {
        try {
            $lec = (float)($input['txtlecture'] ?? 0);
            $tut = (float)($input['txttutorial'] ?? 0);
            $prac = (float)($input['txtpractical'] ?? 0);
            $oth = (float)($input['txtothers'] ?? 0);
            $nfg = (float)($input['txtnf2f'] ?? 0);
            $nfi = (float)($input['txtindependent'] ?? 0);

            $data = [
                ':content'  => $input['txtcontent'], 
                ':idclo'    => $input['selectCLO'],
                ':lec'      => $lec, 
                ':tut'      => $tut, 
                ':prac'     => $prac, 
                ':oth'      => $oth, 
                ':nfg'      => $nfg, 
                ':nfi'      => $nfi,
                ':slt'      => ($lec + $tut + $prac + $oth + $nfg + $nfi),
                ':kursusid' => $input['txtkursusid'],
                ':idslt'    => $input['txtidslt'],
                ':updated_by' => $userId
            ];

            $success = $this->model->kemaskiniSLT($data);

            if ($success) {
                return [
                    'status' => 'success', 
                    'message' => 'Maklumat SLT berjaya dikemaskini!'
                ];
            } else {
                return [
                    'status' => 'error', 
                    'message' => 'Tiada perubahan dilakukan atau gagal mengemaskini.'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error', 
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }
    } 
    
    public function copySLT($userId, $input) {
        try {
            $toCourseId = $input['txtcourseid'];
            $fromCourseId = $input['selectKursusModal'];

            if (empty($fromCourseId)) {
                return [
                    'status' => 'error', 
                    'message' => 'Sila pilih kursus sumber untuk disalin.'
                ];
            }

            $sltRecords = $this->model->getSLTList($fromCourseId, $input['selectSesiModal']);

            if (empty($sltRecords)) {
                return [
                    'status' => 'error', 
                    'message' => 'Tiada rekod SLT dijumpai pada kursus yang dipilih.'
                ];
            }

            $insertedCount = 0;
            foreach ($sltRecords as $row) {
                $data = [
                    ':content'  => $row['content_outline'],
                    ':idclo'    => $row['id_clo'],
                    ':lec'      => (float)$row['f2f_lecture'],
                    ':tut'      => (float)$row['f2f_tutorial'],
                    ':prac'     => (float)$row['f2f_practical'],
                    ':oth'      => (float)$row['f2f_others'],
                    ':nfg'      => (float)$row['nf2f_guided'],
                    ':nfi'      => (float)$row['nf2f_independent'],
                    ':slt'      => (float)$row['slt'],
                    ':kursusid' => $toCourseId,
                    ':created_by' => $userId
                ];

                // Guna semula fungsi tambahSLT yang kita dah ada!
                $this->model->tambahSLT($data);
                $insertedCount++;
            }

            return [
                'status' => 'success', 
                'message' => "$insertedCount rekod SLT berjaya disalin!"
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error', 
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }
    }

    public function deleteSLT($userId, $input) {
        try {
            $idslt = $input['sltid'];
            $success = $this->model->hapusSLT($idslt, $userId);

            if ($success) {
                return ['status' => 'success', 'message' => 'Maklumat SLT berjaya dihapuskan!'];
            } else {
                return ['status' => 'error', 'message' => 'Gagal menghapuskan maklumat.'];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }

}

?>