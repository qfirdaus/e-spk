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

    public function getActiveSession($config_type, $config_category_award): array
    {
        $sql = "
            SELECT a.*, b.award_desc
            FROM istar_config_date a
            LEFT JOIN lp_award_category b ON a.config_category_award = b.award_category
            WHERE config_type = ?
            AND config_category_award = ?
            AND is_active = ?
            ORDER BY a.id ASC
            LIMIT 1
        ";

        $stmt = $this->ehepa->prepare($sql);

        $stmt->execute([$config_type, $config_category_award, 1]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function existsPermohonan($matrik, $id_session): bool {

        $sql = "
            SELECT 1
            FROM istar_record_application
            WHERE matric_no = ?
            AND session_apply = ?
            LIMIT 1
        ";

        $stmt = $this->ehepa->prepare($sql);

        $stmt->execute([
            $matrik,
            $id_session
        ]);

        return (bool)$stmt->fetchColumn();
    }    

    /**  Save permohonan   */
    function toMysqlDate($date)
    {
        if (!$date) return null;

        $d = DateTime::createFromFormat('d-m-Y', $date);
        return $d ? $d->format('Y-m-d') : null;
    }    
    
    public function savePermohonan($matric_no, $draft, $id_session_apply, $application_type = 'konvo')
    {
        $checkSql = "
            SELECT id
            FROM istar_record_application
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
                UPDATE istar_record_application SET
                    updated_at = NOW()
                WHERE id = ? 
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([
                $existing['id']
            ]);

            $application_id = $existing['id'];

        } else {

            $sql = "
                INSERT INTO istar_record_application (
                    application_type,
                    matric_no,
                    student_name,
                    session_apply,
                    status,
                    agreement,
                    submitted_at
                )
                VALUES ( ?, ?, ?, ?, 1, ?, NOW() )
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([
                $application_type,
                $matric_no,
                $student['nama_penuh'] ?? '',
                $id_session_apply ?? '',
                $agreement
            ]);

            $application_id = $this->ehepa->lastInsertId();
        }


        // buang data untuk elak duplicate
        $this->ehepa->prepare("DELETE FROM istar_record_personal WHERE application_id = ?")
            ->execute([$application_id]);

        $this->ehepa->prepare("DELETE FROM istar_record_participation WHERE application_id = ?")
            ->execute([$application_id]);

        $this->ehepa->prepare("DELETE FROM istar_record_position WHERE application_id = ?")
            ->execute([$application_id]);

        $this->ehepa->prepare("DELETE FROM istar_record_award WHERE application_id = ?")
            ->execute([$application_id]);

        // personal
        if (!empty($draft['dataStudent'])) {
            $item = $draft['dataStudent'];
            $sqlPersonal = "
                INSERT INTO istar_record_personal (
                    application_id, matric_no, student_name, ic_no, birth_of_date, age, state_of_birth,
                    nationality, gender, race, religion, marital_status, hpno, hpno_latest, email,
                    student_status, faculty_code, faculty_name, level_of_study, program_code, program_name,
                    period_of_study, semester_study_latest, pngs, pngk, study_financing, session_in, session_out, created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $this->ehepa->prepare($sqlPersonal)->execute([
                $application_id,
                $item['matrik'] ?? null,
                $item['nama_penuh'] ?? null,
                $item['nokp'] ?? null,
                $this->toMysqlDate($item['tarikh_lahir'] ?? null),
                $item['age'] ?? '',
                $item['negeri_lahir'] ?? '',
                $item['warganegara'] ?? '',
                $item['jantina'] ?? '',
                $item['bangsa'] ?? '',
                $item['agama'] ?? '',
                $item['status_kahwin'] ?? '',
                $item['telno'] ?? '',            
                $item['telno_terkini'] ?? null, 
                $item['email'] ?? null,
                $item['status_pelajar'] ?? '',
                $item['kdfakulti'] ?? '',
                $item['fakulti'] ?? '',
                $item['tahap_pengajian'] ?? '',
                $item['kdprogram'] ?? '',
                $item['program'] ?? '',
                $item['tempoh_program'] ?? '',
                $item['semester_terkini'] ?? '',
                $item['pngs'] ?? '',
                $item['pngk'] ?? '',
                $item['pembiayaan_pengajian'] ?? '',
                $item['sesi_akademik_masuk'] ?? '',
                $item['sesi_akademik_tamat'] ?? ''
            ]);
        }

        // penglibatan
        foreach ($draft['penglibatan'] ?? [] as $item) {

            $sql = "
                INSERT INTO istar_record_participation (
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
                INSERT INTO istar_record_position (
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
                INSERT INTO istar_record_award (
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