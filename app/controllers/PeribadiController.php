<?php
// controllers/PeribadiController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

class PeribadiController
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

        $this->pdoStudent = Database::getInstance('sybase_asisdb')->getConnection();
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

    public function getCurrentUserDetailsInfo(): array
    {
        $matrik = trim((string)($_SESSION['f_stafID'] ?? '')); 
        if ($matrik === '') {
            return $this->studentprofile = $this->emptyProfile($this->userModel->getAvatarUrl(null));
        }

        $sql = "SELECT a.*, b.f015keterangan as warganegara_desc, c.f015keterangan as negeri_lahir, d.f021keterangan as status_kahwin 
                FROM v210 a
                LEFT JOIN t015kewarganegaraan b ON a.kewarganegaraan = b.f015kdnegeri
                LEFT JOIN t015negeri c ON a.neglahir = c.f015kdnegeri
                LEFT JOIN t021kahwin d ON a.kdkahwin = d.f021kdkahwin
                WHERE matrik = :matrik AND statuskategori = 'AKTIF'";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->bindValue(':matrik', (int)$matrik, PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$student) {
            return $this->studentprofile = $this->emptyProfile(base_url('assets/images/no-image.jpg'));
        }        
        
        // Avatar: guna nilai f_nopekerja dari hasil query (BUKAN dari session)
        $avatar  = $this->getStudentAvatarUrl((string)$student['matrik'] ?? null);
        $nama    = trim((string)($student['nama'] ?? ''));
        $nick    = trim((string)($student['nama'] ?? ''));
        $display = $nama !== '' ? $nama : ($nick !== '' ? $nick : 'Pengguna');

        return $this->studentprofile = [
            'matrik'     => (string)($student['matrik'] ?? ''),
            'fakulti'    => (string)($student['fakulti'] ?? ''),
            'program'       => (string)($student['program'] ?? ''),
            'nokp'    => (string)($student['nokp'] ?? ''),
            'email'       => (string)($student['alfateh'] ?? ''),            
            'notel_terkini'       => (string)($student['notel_terkini'] ?? ''),
            'jantina'       => (string)($student['jantina'] ?? ''),
            'agama'       => (string)($student['agama'] ?? ''),
            'bangsa'       => (string)($student['bangsa'] ?? ''),
            'warganegara'       => (string)($student['warganegara_desc'] ?? ''),
            'negeri_lahir'       => (string)($student['negeri_lahir'] ?? ''),
            'status_kahwin'       => (string)($student['status_kahwin'] ?? ''),
            'tarikh_lahir'       => (string)($student['thlahir'] ?? ''),
            'alamat1'       => (string)($student['alamat1'] ?? ''),
            'alamat2'       => (string)($student['alamat2'] ?? ''), 
            'alamat3'       => (string)($student['alamat3'] ?? ''),
            'alamat4'       => (string)($student['alamat4'] ?? ''),
            'negeri'       => (string)($student['negeri'] ?? ''),
            'avatar_url' => (string)($avatar ?: base_url('assets/images/no-image.jpg')),
        ];        
    }    
}
