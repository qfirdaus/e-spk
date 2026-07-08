<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/MaklumatKursusUniversiti.php';

class MaklumatKursusUniversitiController
{
    private MaklumatKursusUniversiti $model;
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

        $this->model = new MaklumatKursusUniversiti($this->pdoSPK, $this->pdoStudent);   
        
        // Jalankan fungsi semakan POST secara automatik jika ada tindakan dibuat
        $this->handlePostRequest();
    }

    /**Carian Pengajian & Pemilihan Sesi, Kategori Kursus, Penyelaras, Reset */
    private function handlePostRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST["selectPengajian"])) {
                $_SESSION["pengajiankursus"] = $_POST["selectPengajian"];
            }

            if (isset($_POST["selectSesi_kursus"])) {
                $_SESSION["sesikursus"] = $_POST["selectSesi_kursus"];   
            }

            if (isset($_POST["selectKategoriKursus"]) && $_POST["selectKategoriKursus"] !== "0") {
                $idKursus = $_POST["txtIdKursus"] ?? ''; 
                $kategoriKursus = trim($_POST["selectKategoriKursus"]);
                $updatedBy = $_SESSION['f_stafID'] ?? ''; 

                if (!empty($idKursus)) {
                    try {
                        $success = $this->model->updateKategoriKursus((int)$idKursus, $kategoriKursus, $updatedBy);
                    
                        if ($success) {
                            $_SESSION['flash_alert'] = [
                                'icon'    => 'success',
                                'title'   => h(tr('berjaya', 'Berjaya!')),
                                'message' => h(tr('kemaskini_berjaya', 'Kategori kursus berjaya dikemaskini.'))
                            ];
                        } else {
                            $_SESSION['flash_alert'] = [
                                'icon'    => 'error',
                                'title'   => h(tr('gagal', 'Gagal!')),
                                'message' => h(tr('kemaskini_gagal', 'Kemaskini kategori kursus gagal.'))
                            ];
                        }
                        
                        header('Location: index.php');
                        exit();

                    } catch (Throwable $e) {
                        $this->errorMessage = $e->getMessage();
                    }
                }
            }

            if (isset($_POST["selectPenyelaras"])) {
                $idKursus = $_POST["txtIdKursus"] ?? '';
                $updatedBy = $_SESSION['id_staf'] ?? '';
                $nilaiPilih = $_POST["selectPenyelaras"];

                if (!empty($idKursus)) {
                    $penyelaras = ($nilaiPilih === "0") ? null : $nilaiPilih;

                    try {
                        $success = $this->model->updatePenyelarasKursus((int)$idKursus, $penyelaras, $updatedBy);
                        
                        if ($success) {
                            $_SESSION['flash_alert'] = [
                                'icon'    => 'success',
                                'title'   => 'Berjaya!',
                                'message' => ($nilaiPilih === "0") ? 'Maklumat penyelaras berjaya diset semula.' : 'Maklumat penyelaras berjaya dikemaskini.'
                            ];
                        } else {
                            $_SESSION['flash_alert'] = [
                                'icon'    => 'error',
                                'title'   => 'Gagal!',
                                'message' => 'Kemaskini maklumat penyelaras gagal.'
                            ];
                        }
                        
                        header('Location: index.php');
                        exit();
                        
                    } catch (Throwable $e) {
                        $this->errorMessage = $e->getMessage();
                    }
                }
            }          

            // 4. Jika form penapis biasa (carian) yang dihantar, baru benarkan redirect utama ini run
            if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi_kursus"])) {
                header('Location: index.php');
                exit();
            }
        }

        // Set default 
        $_SESSION["pengajiankursus"] = $_SESSION["pengajiankursus"] ?? '';
        $_SESSION["sesikursus"] = $_SESSION["sesikursus"] ?? '';
    }

    /**set WHERE berdasarkan Tahap Pengajian*/
    private function getKodTerm(): string
    {
        $pengajian = $_SESSION["pengajiankursus"] ?? '';
        if ($pengajian === "Asasi") {
            return "f005term like 'B%'";
        } else if ($pengajian === "Diploma") {
            return "f005term like 'E%'";
        } else if ($pengajian === "Sarjana Muda") {
            return "f005term like 'A%'";
        }
        return "1=1"; // Default jika tiada pilihan supaya sql tidak ralat
    }

    /** Kumpul semua data yang diperlukan oleh halaman utama (View) */
    public function getHalamanData(): array
    {
        $programUniversiti = 'Universiti';
        $sesiKursus = $_SESSION["sesikursus"] ?? '';
        $tahapPengajian = $_SESSION["pengajiankursus"] ?? '';
        $kodTerm = $this->getKodTerm();
        $stafID = $_SESSION['f_stafID'] ?? '';

        try {
            return [
                'list_sesi'               => $this->model->getSesiList($kodTerm),                
                'selected_term_detail'    => $this->model->getSelectedTermDetail($sesiKursus),
                'list_subject_ditawarkan' => $this->model->getKursusDitawarkan($sesiKursus, $stafID),
                'list_subject_registered' => $this->model->getSenaraiKursusTelahDaftar($sesiKursus, $programUniversiti),
                'list_subject_all'        => $this->model->getAllSubjectByTerm($sesiKursus)
            ];
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [
                'list_sesi' => [], 'selected_term_detail' => [],
                'list_subject_registered' => [],  'list_subject_all' => []
            ];
        }
    }

    // Save Kursus Baharu
    public function saveKursus($matrik, $formData)
    {
        try {          
            $formData['created_by'] = $matrik;

            $isSaved = $this->model->addKursusBaharu($formData);

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

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}