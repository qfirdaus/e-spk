<?php
// controllers/KeluargaController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../models/Keluarga.php';

class KeluargaController
{
    private Keluarga $model;
    private PDO $pdoStudent;
    private User $userModel;
    private string $errorMessage = '';

    public function __construct(?PDO $pdoMysql = null)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $pdoStudent = Database::pdoSybaseStudent();
        if (!$pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }
        
        $lang = $_SESSION['lang'] ?? 'ms';
        $pdoEhepa = Database::pdoMysql();
        $pdoEhepa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);     

        $this->userModel = new User(Database::pdoMysql());

        $this->model = new Keluarga($pdoStudent, $pdoEhepa, $this->userModel);
    }

    public function getFamilySAPInfo(): array
    {
        try{
            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));
            
            return $this->model->getFamilySAPDetails($matrik);

        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    public function getFatherInfo(): array
    {
        try{
            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));
            
            $fatherData = $this->model->getFatherDetails($matrik);
            return $fatherData ?: [];

        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    public function getMotherInfo(): array
    {
        try{
            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));
            
            $motherData = $this->model->getMotherDetails($matrik);
            return $motherData ?: [];

        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }    

    // ######## LOOKUP ###########
    /** Get lookup data */
    public function getAllLookup(): array
    {
        return [
            'kategori_oku' => $this->getLookupKategoriOKU(),
            'residence_category' => $this->getLookupResidenceCategory(),
            'negeri' => $this->getLookupNegeri(),
            'negara' => $this->getLookupNegara(),
            'employment_status' => $this->getLookupEmploymentStatus(),
            'employment_sector' => $this->getLookupEmploymentSector(),
            'uniform_service' => $this->getLookupUniformService(),
            'uniform_service_status' => $this->getLookupUniformServiceStatus(),
            'salary_range' => $this->getLookupSalaryRange()
        ];
    }    

    public function getLookupKategoriOKU(): array
    {
        try {
            return $this->model->getKategoriOKULookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }        
    }  
    
    public function getLookupResidenceCategory(): array
    {
        try {
            return $this->model->getResidenceCategoryLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    public function getLookupNegeri(): array
    {
        try {
            return $this->model->getNegeriLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }  
    
    public function getLookupNegara(): array
    {
        try {
            return $this->model->getNegaraLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }    

    public function getLookupSalaryRange(): array
    {
        try {
            return $this->model->getSalaryRangeLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    public function getLookupEmploymentStatus(): array
    {
        try {
            return $this->model->getEmploymentStatusLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }  
    public function getLookupEmploymentSector(): array
    {
        try {
            return $this->model->getEmploymentSectorLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }        
    } 

    public function getLookupUniformService(): array
    {
        try {
            return $this->model->getUniformServiceLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }     
    }     

    public function getLookupUniformServiceStatus(): array
    {
        try {
            return $this->model->getUniformServiceStatusLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }     
    }  

    // submit form
    public function submitDataBapa($matrik, $formData)
    {
        $document_path = null; 
        $document_path_income = null;

        if (isset($formData['dokumen_oku_file']) && $formData['dokumen_oku_file']['error'] == UPLOAD_ERR_OK) {
            $file = $formData['dokumen_oku_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $newFileName = $matrik . '_' . time() . '.' . $fileExtension;
            
            $path = 'pages/rekod-utama/data-keluarga/uploads/kesihatan/';
            $uploadDir = dirname(__DIR__) . '/' . $path;            
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                $document_path = 'pages/rekod-utama/data-keluarga/uploads/kesihatan/' . $newFileName; 
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal memindahkan fail ke folder pelayan. Sila semak kebenaran penulisan folder (permission).'
                ];
            }
        }

        
        if (isset($formData['dokumen_income_file']) && $formData['dokumen_income_file']['error'] == UPLOAD_ERR_OK) {
            $file = $formData['dokumen_income_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $newFileName = $matrik . '_' . time() . '.' . $fileExtension;
            
            $path = 'pages/rekod-utama/data-keluarga/uploads/pendapatan/';
            $uploadDir = dirname(__DIR__) . '/' . $path;            
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                $document_path_income = 'pages/rekod-utama/data-keluarga/uploads/pendapatan/' . $newFileName; 
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal memindahkan fail ke folder pelayan. Sila semak kebenaran penulisan folder (permission).'
                ];
            }
        }

        $formData['dokumen_oku'] = $document_path;
        $formData['dokumen_income'] = $document_path_income;

        try {
            // Panggil fungsi model
            $isSaved = $this->model->saveDataBapa($matrik, $formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Maklumat berjaya disimpan'
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

    public function submitDataIbu($matrik, $formData)
    {
        $document_path = null; 
        $document_path_income = null;

        if (isset($formData['dokumen_oku_file']) && $formData['dokumen_oku_file']['error'] == UPLOAD_ERR_OK) {
            $file = $formData['dokumen_oku_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $newFileName = $matrik . '_' . time() . '.' . $fileExtension;
            
            $path = 'pages/rekod-utama/data-keluarga/uploads/kesihatan/';
            $uploadDir = dirname(__DIR__) . '/' . $path;            
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                $document_path = 'pages/rekod-utama/data-keluarga/uploads/kesihatan/' . $newFileName; 
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal memindahkan fail ke folder pelayan. Sila semak kebenaran penulisan folder (permission).'
                ];
            }
        }

        
        if (isset($formData['dokumen_income_file']) && $formData['dokumen_income_file']['error'] == UPLOAD_ERR_OK) {
            $file = $formData['dokumen_income_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $newFileName = $matrik . '_' . time() . '.' . $fileExtension;
            
            $path = 'pages/rekod-utama/data-keluarga/uploads/pendapatan/';
            $uploadDir = dirname(__DIR__) . '/' . $path;            
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                $document_path_income = 'pages/rekod-utama/data-keluarga/uploads/pendapatan/' . $newFileName; 
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal memindahkan fail ke folder pelayan. Sila semak kebenaran penulisan folder (permission).'
                ];
            }
        }

        $formData['dokumen_oku'] = $document_path;
        $formData['dokumen_income'] = $document_path_income;

        try {
            // Panggil fungsi model
            $isSaved = $this->model->saveDataIbu($matrik, $formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Maklumat berjaya disimpan'
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
