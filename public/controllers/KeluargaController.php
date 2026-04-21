<?php
// controllers/KeluargaController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

class KeluargaController
{
    private const STUDENT_AVATAR_BASE_URL = 'https://kemasukan.upnm.edu.my/tawaran/pelajar/student_image/';

    public string $lang = 'ms';
    public array  $profile = [];
    public array  $studentprofile = [];

    private PDO  $pdoMysql;
    private User $userModel;
    private PDO  $pdoStudent;

    public function __construct(?PDO $pdoMysql = null)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $this->lang     = $_SESSION['lang'] ?? 'ms';
        $this->pdoMysql = $pdoMysql ?: Database::pdoMysql();
        $this->pdoMysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->userModel = new User($this->pdoMysql);
        $this->profile   = [];

        $pdoStudent = Database::pdoSybaseStudent();
        if (!$pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }
        $this->pdoStudent = $pdoStudent;
        $this->studentprofile = [];
    }

    public function getLang(): string { return $this->lang; }

    private function emptyProfile(?string $avatar): array
    {
        return [
            'stafID'     => '',
            'nopekerja'  => '',
            'nama_penuh' => 'Pengguna',
            'nickname'   => '',
            'jawatan'    => '',
            'gred'       => '',
            'jabatan'    => '',
            'emel'       => '',
            'avatar_url' => (string)($avatar ?: base_url('assets/images/no-image.jpg')),
        ];
    }

    private function getStudentAvatarUrl(string $matrik): string
    {
        $clean = preg_replace('/\D+/', '', $matrik) ?? '';
        if ($clean === '') return base_url('assets/images/no-image.jpg');
        return self::STUDENT_AVATAR_BASE_URL . rawurlencode($clean) . '.jpg';
    }

    public function getCurrentParentDetailsInfo(): array
    {
        $matrik = trim((string)($_SESSION['f_stafID'] ?? '')); 
        if ($matrik === '') {
            return $this->studentprofile = $this->emptyProfile($this->userModel->getAvatarUrl(null));
        }

        $sql = "SELECT a.*
                FROM v210 a
                LEFT JOIN t015kewarganegaraan b ON a.kewarganegaraan = b.f015kdnegeri
                LEFT JOIN t015negeri c ON a.neglahir = c.f015kdnegeri
                LEFT JOIN t021kahwin d ON a.kdkahwin = d.f021kdkahwin
                WHERE convert(varchar(50), a.matrik) = :matrik
                  AND upper(convert(varchar(20), a.statuskategori)) = 'AKTIF'";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->bindValue(':matrik', $matrik);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$student) {
            return $this->studentprofile = $this->emptyProfile(base_url('assets/images/no-image.jpg'));
        }        

        return $this->studentprofile = [
            'nama_bapa'     => (string)($student['namabapa'] ?? ''),
            'nokpbapa'      => (string)($student['nokpbapa'] ?? ''),
            'nohp_bapa'     => (string)($student['nohp_bapa'] ?? ''),
            'nama_ibu'      => (string)($student['namaibu'] ?? ''),
            'nokpibu'       => (string)($student['nokpibu'] ?? ''),
            'nohp_ibu'      => (string)($student['nohp_ibu'] ?? ''),
        ];        
    }    

    public function getSalaryRange(): array
    {
        $sql = "SELECT * FROM lp_salary";

        $stmt = $this->pdoMysql->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null; //fetch all rows

        $salaryRanges = [];
        foreach ($rows as $row) {
            $min = (int)$row['min_salary'];
            $max = isset($row['max_salary']) ? (int)$row['max_salary'] : null;

            // Buat label untuk dropdown
            if ($min == 0 && $max !== null) {
                $label = '< RM' . $max;
            } elseif ($max === null) {
                $label = '> RM' . $min;
            } else {
                $label = 'RM' . $min . ' – RM' . $max;
            }

            $salaryRanges[] = [
                'value' => $min, // submit min_salary sebagai value
                'label' => $label
            ];
        }

        return $salaryRanges;
    }

    public function getEmploymentStatus(): array
    {
        $sql = "SELECT * FROM lp_employment_status";

        $stmt = $this->pdoMysql->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null; //fetch all rows

        $employmentStatuses = [];
        foreach ($rows as $row) {
            $employmentStatuses[] = [
                'statusCode' => $row["status_code"],
                'statusMY' => $row["status_my"], 
                'statusEN' => $row["status_en"], 
            ];
        }
        
        return $employmentStatuses;
    }  
    public function getEmploymentSector(): array
    {
        $sql = "SELECT * FROM lp_employment_sector";

        $stmt = $this->pdoMysql->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null; //fetch all rows

        $employmentSectors = [];
        foreach ($rows as $row) {
            $employmentSectors[] = [
                'sectorCode' => $row["sector_code"],
                'sectorMY' => $row["sector_my"], 
                'sectorEN' => $row["sector_en"], 
            ];
        }
        
        return $employmentSectors;
    } 

    public function getUniformService(): array
    {
        $sql = "SELECT * FROM lp_uniform_service_type";

        $stmt = $this->pdoMysql->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null; //fetch all rows

        $uniformServices = [];
        foreach ($rows as $row) {
            $uniformServices[] = [
                'serviceCode' => $row["service_code"],
                'serviceMY' => $row["service_my"], 
                'serviceEN' => $row["service_en"], 
            ];
        }
        
        return $uniformServices;
    }     
}
