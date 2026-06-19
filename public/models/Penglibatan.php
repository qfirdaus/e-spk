<?php

class Penglibatan
{
    private PDO $istad;
    private PDO $ehepa;

    public function __construct(PDO $istad, PDO $ehepa)
    {
        $this->istad = $istad;
        $this->ehepa = $ehepa;
    }

    public function getAllKegiatan(string $matrik): array
    {
        //peserta kegiatan pelajar
        $sql = "
            SELECT
                id_kegiatan_pelajar,
                matrik,
                'PESERTA' AS pencapaian,
                'ISTAD' AS sumber,
                nama_kegiatan_pelajar AS nama,
                tarikh_mula AS tarikh,
                tarikh_tamat,
                kod_sesi,
                NULL AS wakil,
                NULL AS peringkat                
            FROM v_kehadiran_kegiatan_pelajar
            WHERE nama_kegiatan_pelajar IS NOT NULL AND matrik = :matrik
        ";

        $stmt = $this->istad->prepare($sql);

        $stmt->execute(['matrik' => $matrik]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllJawatan(string $matrik): array
    {
        //jawatan kegiatan pelajar & badan pelajar
        $sql = "
            SELECT
                nostaf_matrik AS matrik,
                id_jawatan,
                jawatan,
                'ISTAD' AS sumber,
                kod_kategori_aktiviti,
                kategori_aktiviti,
                id_kegiatan_pelajar AS id_kegiatan_badan,
                nama_kegiatan_pelajar AS nama_bp_program,
                tarikh_mula,
                tarikh_tamat,
                kod_sesi
            FROM v_ahli_kegiatan_pelajar
            WHERE nostaf_matrik = :matrik1

            UNION

            SELECT
                matrik,
                id_jawatan,
                jawatan,
                'ISTAD' AS sumber,
                kod_kategori_aktiviti,
                kategori_aktiviti,                
                id_ahli_bp AS id_kegiatan_bp,
                nama_badan_pelajar AS nama_bp_program,
                tarikh_mula,
                tarikh_tamat,
                kod_sesi
            FROM v_ahli_badan_pelajar
            WHERE matrik = :matrik2
        ";

        $stmt = $this->istad->prepare($sql);

       $stmt->execute(['matrik1' => $matrik, 'matrik2' => $matrik]);    

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**  Get lookup data   */
    public function getWakilLookup(): array
    {
        $sql = "
            SELECT idwakil, wakil_code, wakil_my
            FROM lp_representative
            ORDER BY idwakil ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeringkatLookup(): array
    {
        $sql = "
            SELECT idperingkat, peringkat_code, peringkat_my
            FROM lp_level
            ORDER BY idperingkat ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPencapaianLookup(): array
    {
        $sql = "
            SELECT idpencapaian, pencapaian_code, pencapaian_my
            FROM lp_achievement
            ORDER BY idpencapaian ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getJawatanLookup(): array
    {
        $sql = "
            SELECT id_jawatan, keterangan, keteranganBP
            FROM tbl_jawatan
            ORDER BY sort ASC
        ";

        $stmt = $this->istad->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
    public function getKategoriPerjawatanLookup(): array
    {
        $sql = "
            SELECT id, kod_kategori_aktiviti, kategori_aktiviti
            FROM lp_kategori_aktiviti
            ORDER BY id ASC
        ";

        $stmt = $this->istad->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**  Get lookup data   */

    public function getActiveSession(): array
    {
        $sql = "
            SELECT *
            FROM istar_config_date
            WHERE config_type = ?
            AND config_category_award = ?
            AND is_active = ?
            ORDER BY id ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute(['APPLICATION','pingat_graduan',1]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**  Save permohonan   */
    function toMysqlDate($date)
    {
        if (!$date) return null;

        $d = DateTime::createFromFormat('d-m-Y', $date);
        return $d ? $d->format('Y-m-d') : null;
    }    
    
    public function savePermohonan($matric_no, $draft, $application_type = 'konvo')
    {
        $checkSql = "
            SELECT id
            FROM istar_application
            WHERE matric_no = ?
            AND application_type = ?
            LIMIT 1
        ";

        $checkStmt = $this->ehepa->prepare($checkSql);
        $checkStmt->execute([$matric_no, $application_type]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        $student = $draft['dataStudent'] ?? [];
        $perakuan = $draft['perakuan'] ?? [];

        $agreement = (
            ($perakuan['chk1'] ?? 0) &&
            ($perakuan['chk2'] ?? 0) &&
            ($perakuan['chk3'] ?? 0)
        ) ? 1 : 0;

        if ($existing) {

            $sql = "
                UPDATE istar_application SET
                    student_name = ?,
                    ic_no = ?,
                    email = ?,
                    faculty_code = ?,
                    faculty_name = ?,
                    program_code = ?,
                    program_name = ?,
                    semester = ?,
                    agreement = ?,
                    updated_at = NOW()
                WHERE id = ?
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([
                $student['nama_penuh'] ?? '',
                $student['nokp'] ?? '',
                $student['email'] ?? '',
                $student['kdfakulti'] ?? '',
                $student['fakulti'] ?? '',
                $student['kdprogram'] ?? '',
                $student['program'] ?? '',
                $student['semester_terkini'] ?? '',
                $agreement,
                $existing['id']
            ]);

            $application_id = $existing['id'];

        } else {

            $sql = "
                INSERT INTO istar_application (
                    matric_no,
                    application_type,

                    student_name,
                    ic_no,
                    email,

                    faculty_code,
                    faculty_name,

                    program_code,
                    program_name,

                    semester,
                    agreement,

                    status,
                    submitted_at,
                    updated_at
                )
                VALUES (
                    ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    1,
                    NOW(),
                    NOW()
                )
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([
                $matric_no,
                $application_type,

                $student['nama_penuh'] ?? '',
                $student['nokp'] ?? '',
                $student['email'] ?? '',

                $student['kdfakulti'] ?? '',
                $student['fakulti'] ?? '',

                $student['kdprogram'] ?? '',
                $student['program'] ?? '',

                $student['semester_terkini'] ?? '',
                $agreement
            ]);

            $application_id = $this->ehepa->lastInsertId();
        }


        // buang data untuk elak duplicate
        $this->ehepa->prepare("DELETE FROM istar_application_participation WHERE application_id = ?")
            ->execute([$application_id]);

        $this->ehepa->prepare("DELETE FROM istar_application_position WHERE application_id = ?")
            ->execute([$application_id]);

        $this->ehepa->prepare("DELETE FROM istar_application_award WHERE application_id = ?")
            ->execute([$application_id]);


        // penglibatan
        foreach ($draft['penglibatan'] ?? [] as $item) {

            $sql = "
                INSERT INTO istar_application_participation (
                    application_id,
                    source,
                    external_id,
                    name_programme,
                    programme_date,
                    representative,
                    level,
                    achievement,
                    document_path
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $this->ehepa->prepare($sql)->execute([
                $application_id,
                $item['sumber'] ?? null,
                $item['id'] ?? null,
                $item['nama'] ?? null,
                $this->toMysqlDate($item['tarikh'] ?? null),
                $item['wakil'] ?? '',
                $item['peringkat'] ?? '',
                $item['pencapaian'] ?? '',
                $item['dokumen_path'] ?? ''
            ]);
        }


        // jawatan
        foreach ($draft['jawatan'] ?? [] as $item) {

            $sql = "
                INSERT INTO istar_application_position (
                    application_id,
                    external_id,
                    position_id,
                    position_name,
                    category_code,
                    level,
                    appointment_date
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $this->ehepa->prepare($sql)->execute([
                $application_id,
                $item['id'] ?? null,
                $item['id_jawatan'] ?? null,
                $item['jawatan'] ?? null,
                $item['kod_kategori_aktiviti'] ?? null,
                $item['peringkat'] ?? null,
                $this->toMysqlDate($item['tarikh_lantikan'] ?? null)
            ]);
        }


        // anugerah
        foreach ($draft['anugerah'] ?? [] as $item) {

            $sql = "
                INSERT INTO istar_application_award (
                    application_id,
                    external_id,
                    award_name,
                    year,
                    issuer,
                    level
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $this->ehepa->prepare($sql)->execute([
                $application_id,
                $item['id'] ?? null,
                $item['nama_anugerah'] ?? null,
                $item['tahun'] ?? null,
                $item['kurniaan_pemberian'] ?? null,
                $item['peringkat'] ?? null
            ]);
        }


        return true;
    }

    /**  Test connection   */
    public function testConnection(): bool
    {
        $stmt = $this->istad->query("SELECT 1");
        return (bool) $stmt->fetchColumn();
    }
}