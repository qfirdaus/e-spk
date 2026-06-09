<?php

class Peribadi
{
    private PDO $dbStudent;
    private User $userModel;

    public function __construct(PDO $pdoStudent, User $userModel)
    {
        $this->dbStudent = $pdoStudent;
        $this->userModel = $userModel;
    }

    public function getStudentByMatrik(string $matrik): ?array
    {
        $sql = "SELECT a.*, 
                       b.f015keterangan as warganegara_desc,
                       c.f015keterangan as negeri_lahir,
                       d.f021keterangan as status_kahwin,
                       e.f005sesi as semester_terkini,
                       f.f005sesi as semester_masuk,
                       g.f005sesi as semester_tamat
                FROM v210_eHEPA a
                LEFT JOIN t015kewarganegaraan b ON a.kewarganegaraan = b.f015kdnegeri
                LEFT JOIN t015negeri c ON a.neglahir = c.f015kdnegeri
                LEFT JOIN t021kahwin d ON a.kdkahwin = d.f021kdkahwin
                LEFT JOIN v005term e ON a.semsemasa = e.f005term
                LEFT JOIN v005term f ON a.sesimasuk = f.f005term
                LEFT JOIN v005term g ON a.sesitamat = g.f005term                
                WHERE convert(varchar(50), a.matrik) = :matrik
                  AND upper(convert(varchar(20), a.statuskategori)) = 'AKTIF'";

        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute(['matrik' => $matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function emptyProfile(string $avatar): array
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
            'avatar_url' => $avatar,
        ];
    }

    public function getAvatar(string $matrik, string $baseUrl): string
    {
        $clean = preg_replace('/\D+/', '', $matrik) ?? '';

        if ($clean === '') {
            return $baseUrl;
        }

        return 'https://kemasukan.upnm.edu.my/tawaran/pelajar/student_image/' . rawurlencode($clean) . '.jpg';
    }

    public function formatStudent(array $student, string $avatar): array
    {
        $age = '';
        if (!empty($student['thlahir'])) {
            try {
                $age = $this->count_age(new DateTime($student['thlahir']));
            } catch (Throwable $e) {
                $age = '';
            }
        }
        $tempoh = (float)($student['bilsem'] ?? 0);
        $tempoh_program =      rtrim(rtrim(number_format($tempoh, 2), '0'), '.') . ' Tahun (' . ((int)$tempoh * 2) . ' Semester)';

        return [
            'matrik' => (string)($student['matrik'] ?? ''),
            'nama_penuh' => (string)($student['nama'] ?? ''),
            'nokp'   => (string)($student['nokp'] ?? ''),
            'email'  => (string)($student['alfateh'] ?? $student['email'] ?? ''),
            'notel_terkini' => (string)($student['notel_terkini'] ?? $student['telno_terkini'] ?? $student['hpno'] ?? ''),
            'hpno'   => (string)($student['hpno'] ?? ''),
            'telno'  => (string)($student['telno'] ?? ''),
            'telno_terkini' => (string)($student['telno_terkini'] ?? ''),
            'jantina' => (string)($student['jantina'] ?? ''),
            'agama'   => (string)($student['agama'] ?? ''),
            'bangsa'  => (string)($student['bangsa'] ?? ''),
            'warganegara' => (string)($student['warganegara_desc'] ?? ''),
            'negeri_lahir' => (string)($student['negeri_lahir'] ?? ''),
            'status_kahwin' => (string)($student['status_kahwin'] ?? ''),
            'tarikh_lahir' => (string)($student['thlahir'] ?? ''),
            'age' => $age,
            'kdfakulti' => (string)($student['kdfakulti'] ?? ''),
            'fakulti' => (string)($student['fakulti'] ?? ''),
            'kdprogram' => (string)($student['kdprogram'] ?? ''),
            'program' => (string)($student['program'] ?? ''),
            'program_pengajian' => (string)($student['program'] ?? ''),
            'kdtahap' => (string)($student['kdtahap'] ?? ''),
            'tahap_pengajian' => (string)($student['tahap_pengajian'] ?? ''),
            'status' => (string)($student['status'] ?? ''),
            'statusketerangan' => (string)($student['statusketerangan'] ?? ''),
            'status_pengajian' => (string)($student['statusketerangan'] ?? ''), 
            'statuskategori' => (string)($student['statuskategori'] ?? ''),
            'semester_terkini' => (string)($student['semester_terkini'] ?? ''),
            'semester_masuk' => (string)($student['semester_masuk'] ?? ''),
            'semester_tamat' => (string)($student['semester_tamat'] ?? '-'),
            'sesi_akademik_masuk' => (string)($student['semester_masuk'] ?? ''),
            'sesi_akademik_tamat' => (string)($student['semester_tamat'] ?? ''),            
            'pngs' => ((string)($student['release']) == 1) ? (string)($student['pngs'] ?? '') : '',
            'pngk' => ((string)($student['release']) == 1) ? (string)($student['pngk'] ?? '') : '',
            'alamat1' => (string)($student['alamat1'] ?? ''),
            'alamat2' => (string)($student['alamat2'] ?? ''),
            'alamat3' => (string)($student['alamat3'] ?? ''),
            'alamat4' => (string)($student['alamat4'] ?? ''),
            'negeri' => (string)($student['negeri'] ?? ''),
            'kategori_kadet' => (string)($student['kategori_kadet'] ?? ''),
            'kadet' => (string)($student['kadet'] ?? ''),
            'tahun_pengajian' => (string)($student['tahun_pengajian'] ?? ''),
            'tempoh_program' => $tempoh_program,        
            'status_pelajar' => ($student['kategori_kadet'] ?? '') === 'Pkdt' ? 'Kadet ' . ($student['kadet'] ?? '') : ($student['kadet'] ?? ''),
            'avatar_url' => $avatar,         
        ];
    }

    public function count_age(DateTime $tarikh_lahir): string {
        $today = new DateTime();
        $diff = $today->diff($tarikh_lahir);
        return $diff->y . ' tahun ' . $diff->m . ' bulan ' . $diff->d . ' hari';
    }

}