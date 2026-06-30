<?php
// controllers/RekodPeribadiController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/RekodPeribadi.php';

class RekodPeribadiController
{
    private RekodPeribadi $model;
    private string $errorMessage = '';

    public function __construct()
    {                
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
        $lang = $_SESSION['lang'] ?? 'ms';
        $pdoEhepa = Database::pdoMysql();
        $pdoEhepa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new RekodPeribadi($pdoEhepa);      
    }

    public function getPekerjaanData($matrik)
    {
        try {
            return $this->model->getDataPekerjaan($matrik);
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }  
    
    public function getKesihatanData($matrik)
    {
        try {
            return $this->model->getDataKesihatan($matrik);
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }
    
    public function getAkaunData($matrik)
    {
        try {
            return $this->model->getDataAkaun($matrik);
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }
    
    public function getSponsorData($matrik)
    {
        try {
            return $this->model->getDataSponsor($matrik);
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
            'status_kerja' => $this->getLookupStatusKerja(),
            'sektor_kerja' => $this->getLookupSektorKerja(),
            'negeri' => $this->getLookupNegeri(),
            'negara' => $this->getLookupNegara(),
            'kategori_oku' => $this->getLookupKategoriOKU(),
            'bank' => $this->getLookupBank(),
            'sponsor' => $this->getLookupSponsor()
        ];
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
    
    public function getLookupStatusKerja(): array
    {
        try {
            return $this->model->getStatusKerjaLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }        
    }

    public function getLookupSektorKerja(): array
    {
        try {
            return $this->model->getSektorKerjaLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }        
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
       
    public function getLookupBank(): array
    {
        try {
            return $this->model->getBankLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }        
    }   
    
    public function getLookupSponsor(): array
    {
        try {
            return $this->model->getSponsorLookup();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }        
    }    

    /** Get lookup data */

    public function submitDataSponsor($matrik, $formData)
    {       
        try {
            // Panggil fungsi model
            $isSaved = $this->model->saveDataSponsor($matrik, $formData);

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

    public function submitPekerjaan($matrik, $formData)
    {
        try {
            $this->model->savePekerjaan($matrik, $formData);

            return [
                'status' => 'success',
                'message' => 'Maklumat pekerjaan berjaya disimpan'
            ];

        } catch (Exception $e) {

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function submitKesihatan($matrik, $formData)
    {
        $document_path = null; 

        if (isset($formData['dokumen_oku_file']) && $formData['dokumen_oku_file']['error'] == UPLOAD_ERR_OK) {
            $file = $formData['dokumen_oku_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $newFileName = $matrik . '_' . time() . '.' . $fileExtension;
            
            $path = 'pages/rekod-utama/data-peribadi/uploads/kesihatan/';
            $uploadDir = dirname(__DIR__) . '/' . $path;            
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                $document_path = 'pages/rekod-utama/data-peribadi/uploads/kesihatan/' . $newFileName; 
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal memindahkan fail ke folder pelayan. Sila semak kebenaran penulisan folder (permission).'
                ];
            }
        }

        $formData['dokumen_oku'] = $document_path;

        try {
            // Panggil fungsi model
            $isSaved = $this->model->saveKesihatan($matrik, $formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Maklumat kesihatan berjaya disimpan'
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

    public function submitAkaun($matrik, $formData)
    {
        $document_path = null; 

        if (isset($formData['dokumen_akaun_file']) && $formData['dokumen_akaun_file']['error'] == UPLOAD_ERR_OK) {
            $file = $formData['dokumen_akaun_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $newFileName = $matrik . '_' . time() . '.' . $fileExtension;
            
            $path = 'pages/rekod-utama/data-peribadi/uploads/akaun/';
            $uploadDir = dirname(__DIR__) . '/' . $path;            
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                $document_path = 'pages/rekod-utama/data-peribadi/uploads/akaun/' . $newFileName; 
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal memindahkan fail ke folder. Sila semak kebenaran penulisan folder (permission).'
                ];
            }
        }

        $formData['dokumen_akaun'] = $document_path;

        try {
            // Panggil fungsi model
            $isSaved = $this->model->saveAkaun($matrik, $formData);

            if ($isSaved) {
                return [
                    'status' => 'success',
                    'message' => 'Maklumat akaun berjaya disimpan'
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

    public function testConnection()
    {
        return $this->model->testConnection();
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }    
}
