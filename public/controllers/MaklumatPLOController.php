<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatPLO.php';

class MaklumatPLOController
{
    private MaklumatPLO $model;
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private string $errorMessage = '';

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    
        $this->pdoStudent = Database::pdoSybaseStudent();
        if (!$this->pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }

        $this->pdoSPK = Database::pdoMysql();
        $this->pdoSPK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Pasangkan pdoSPK dan pdoStudent ke dalam Model
        $this->model = new MaklumatPLO($this->pdoSPK, $this->pdoStudent);   
        
        // Jalankan fungsi semakan POST secara automatik jika ada carian dibuat
        $this->handleSearchRequest();
    }

    /**
     * Mengendalikan form submission (Carian/Search)
     */
    private function handleSearchRequest(): void
    {
        if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi"]) || isset($_POST["selectProgram"])) {
            $_SESSION["pengajianplo"] = $_POST["selectPengajian"] ?? '';
            $_SESSION["sesiplo"] = $_POST["selectSesi"] ?? '';
            $_SESSION["programplo"] = $_POST["selectProgram"] ?? '';
            
            header('Location: index.php');
            exit();
        }

        // Set default value jika session belum wujud
        $_SESSION["pengajianplo"] = $_SESSION["pengajianplo"] ?? '';
        $_SESSION["sesiplo"] = $_SESSION["sesiplo"] ?? '';
        $_SESSION["programplo"] = $_SESSION["programplo"] ?? '';
    }

    /**
     * Menjana kod penapisan berdasarkan Tahap Pengajian
     */
    private function getKodTerm(): string
    {
        $pengajian = $_SESSION["pengajianplo"] ?? '';
        if ($pengajian === "Asasi") {
            return "f005term like 'B%'";
        } else if ($pengajian === "Diploma") {
            return "f005term like 'E%'";
        } else if ($pengajian === "Sarjana Muda") {
            return "f005term like 'A%'";
        }
        return "";
    }

    /**
     * Mengumpul semua data yang diperlukan oleh halaman utama (View)
     */
    public function getHalamanData(): array
    {
        $programUniversiti = 'Universiti';
        $ptj = $_SESSION['ptj'] ?? '';
        $sesiPlo = $_SESSION["sesiplo"] ?? '';
        $programPlo = $_SESSION["programplo"] ?? '';
        $tahapPengajian = $_SESSION["pengajianplo"] ?? '';

        $kodTerm = $this->getKodTerm();

        try {
            return [
                'list_sesi'       => $this->model->getSesiList($kodTerm),
                'list_program'    => $this->model->getProgramList($tahapPengajian, $ptj),
                'selected_term'   => $this->model->getSelectedTerm($sesiPlo),
                'selected_program'=> $this->model->getSelectedProgram($programPlo),
                'list_peo'        => $this->model->getPeoList($sesiPlo, $programPlo),
                'list_plo'        => $this->model->getPloList($sesiPlo, $programUniversiti),
                'list_mqf'        => $this->model->getMqfList(),
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'list_sesi' => [], 'list_program' => [], 'selected_term' => [],
                'selected_program' => [], 'list_peo' => [], 'list_plo' => [], 'list_mqf' => []
            ];
        }
    }

    public function savePLO($matrik, $formData)
    {
        try {
            $formData['created_by'] = $matrik;

            $isSaved = $this->model->addPloBaharu($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod berjaya disimpan'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengemaskini maklumat ke dalam pangkalan data.'
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }        
    }

    public function updatePLO($matrik, $formData) {
        try {
            $formData['updated_by'] = $matrik;

            $isSaved = $this->model->updateDataPlo($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod berjaya disimpan'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengemaskini maklumat ke dalam pangkalan data.'
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }         
    }   

    public function deletePLO($matrik, $formData) {
        try {
            $formData['updated_by'] = $matrik;

            $isSaved = $this->model->deleteDataPlo($formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod berjaya dihapuskan'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengemaskini maklumat ke dalam pangkalan data.'
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }         
    }   
    
    public function copyPLO($matrik, $formData)
    {
        try {
            $formData['created_by'] = $matrik;

            $isCopied = $this->model->salinPloSesi($formData);

            if ($isCopied) {
                return [
                    'status' => 'success',
                    'message' => 'Rekod PLO berjaya disalin ke sesi baharu.'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyalin maklumat PLO ke dalam pangkalan data.'
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ralat Sistem: ' . $e->getMessage()
            ];
        }        
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}