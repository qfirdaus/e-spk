<?php
declare(strict_types=1);

class MaklumatKursusProgram
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private PDO $pdoStaff;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent, PDO $pdoStaff)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
        $this->pdoStaff = $pdoStaff;
    }

    public function getKodJabatanStaf(string $stafID): string
    {
        $sql = "SELECT kdjbtnhakiki as f_jabatanKod FROM v630staf_service_skim_all WHERE nopekerja = :staf_id";
        $stmt = $this->pdoStaff->prepare($sql);
        $stmt->execute([':staf_id' => trim($stafID)]);

        return $stmt->fetchColumn() ?: '';   
    }

    public function getSesiList(string $tahapPengajian): array
    {
        if (empty($tahapPengajian)) return [];
        
        $sql = "SELECT * FROM v005_spk WHERE $tahapPengajian ORDER BY f005term DESC";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // senarai program berdasarkan tahap pengajian & PTJ/Fakulti staf
    public function getProgramList(string $tahapPengajian, string $ptj): array
    {
        $tahapPengajian = trim($tahapPengajian);
        $ptj = trim($ptj);

        if (empty($tahapPengajian) || empty($ptj)) {
            return [];
        }

        $sql = "SELECT * FROM v006_spk 
                WHERE LTRIM(RTRIM(tahap_pengajian)) = :tahap_pengajian 
                AND LTRIM(RTRIM(kdjbt)) = :ptj 
                ORDER BY program";
                
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([
            ':tahap_pengajian' => $tahapPengajian,
            ':ptj'             => $ptj
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Selected Sesi
    public function getSelectedTermDetail(string $term, string $tahapPengajian): array
    {
        $sql = "SELECT *, LEFT( f005term, LEN( f005term ) -1 ) as term  FROM v005_spk WHERE f005term = :sesi AND $tahapPengajian";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':sesi' => $term]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    //Selected Program
    public function getSelectedProgramDetail(string $idProgram): array
    {
        $sql = "SELECT * FROM v006_spk WHERE id_program = :id_program";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':id_program' => $idProgram]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getKursusProgramList(string $sesi, string $ptj, string $programKursus, string $programUniversiti): array
    {
        $sqlSPK = "SELECT DISTINCT 
                        a.kod_kursus, 
                        a.id_kursus, 
                        a.term_pengajian, 
                        a.kategori_kursus, 
                        a.penyelaras_kursus 
                    FROM spk_tkursus a
                    WHERE a.term_pengajian = :sesikursus 
                    AND a.kod_jabatan = :ptj 
                    AND a.kod_program = :programkursus 
                    AND a.program_universiti = :programuniversiti 
                    ORDER BY a.kod_kursus ASC";

        $stmtSPK = $this->pdoSPK->prepare($sqlSPK);
        $stmtSPK->execute([
            ':sesikursus'        => $sesi,
            ':ptj'               => $ptj,
            ':programkursus'     => $programKursus,
            ':programuniversiti' => $programUniversiti
        ]);
        
        $senaraiKursus = $stmtSPK->fetchAll(PDO::FETCH_ASSOC);

        if (empty($senaraiKursus)) {
            return [];
        }

        $kodKursusList = array_unique(array_column($senaraiKursus, 'kod_kursus'));
        
        $placeholders = implode(',', array_fill(0, count($kodKursusList), '?'));

        $sqlStudent = "SELECT kodk, subjekbm 
                    FROM v270offer_spk 
                    WHERE term = ? 
                        AND kodk IN ($placeholders)";

        $stmtStudent = $this->pdoStudent->prepare($sqlStudent);
        $stmtStudent->execute(array_merge([$sesi], $kodKursusList));
        
        $subjekMap = $stmtStudent->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($senaraiKursus as &$kursus) {
            $kod = $kursus['kod_kursus'];
            $kursus['subjekbm'] = $subjekMap[$kod] ?? null; 
        }

        return $senaraiKursus;
    }
    public function addPloBaharu(array $data): bool 
    {
        $program_universiti = 'Program';
        
        $sesiid       = $data['txtsesiid'] ?? null;
        $programid    = $data["txtprogramid"] ?? null;
        $program      = $data["txtprogram"] ?? null;
        $kodplo       = $data['selectkodplo'] ?? null;
        $kodmqf       = $data['selectkodmqf'] ?? null;
        $keteranganbm = $data['txtketeranganplo'] ?? null;
        $created_by   = $data['created_by'] ?? null;
        $ptj          = $data['ptj'] ?? null;
        $peoIds       = $data['peo_ids'] ?? []; // Checkbox PEO

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "INSERT INTO spk_tplo (program_universiti, kod_plo, keterangan_bm, kod_sesi, kod_jabatan, kod_program, kod_mqf,      created_by, created_date) 
                    VALUES (:program_uni, :kod_plo, :keterangan_bm, :kod_sesi, :kod_jabatan, :kod_program, :kod_mqf, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $result = $stmt->execute([
                ':program_uni'   => $program_universiti,
                ':kod_plo'       => $kodplo,
                ':keterangan_bm' => $keteranganbm,
                ':kod_sesi'      => $sesiid,
                ':kod_jabatan'   => $ptj,
                ':kod_program'   => $programid,
                ':kod_mqf'       => $kodmqf,
                ':created_by'    => $created_by
            ]);

            if (!$result) {
                $this->pdoSPK->rollBack();
                return false;
            }

            // Ambil ID PLO yang baru dimasukkan
            $plo_id = $this->pdoSPK->lastInsertId();

            // Insert ke table pemetaan PEO-PLO 
            if (!empty($peoIds) && $plo_id) {
                $sql_peo = "INSERT INTO spk_tpenetapan_peo_plo (id_peo, id_plo, created_by, created_date) 
                            VALUES (:id_peo, :id_plo, :created_by, NOW())";
                $stmt_peo = $this->pdoSPK->prepare($sql_peo);

                foreach ($peoIds as $peoId) {
                    $stmt_peo->execute([
                        ':id_peo'     => $peoId,
                        ':id_plo'     => $plo_id,
                        ':created_by' => $created_by
                    ]);
                }
            }

            $this->pdoSPK->commit();
            return true;

        } catch (\Exception $e) {
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }
            throw $e;
        }
    }

    public function updateDataPlo(array $data): bool 
    {
        $idplo        = $data['txtidplo'] ?? null;
        $keteranganbm = $data['txtketeranganplo'] ?? null;
        $kodmqf       = $data['selectkodmqf_edit'] ?? null;
        $updated_by   = $data['updated_by'] ?? null;
        $peoIds       = $data['peo_ids'] ?? [];

        if (!$idplo) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tplo 
                    SET keterangan_bm = :keterangan_bm, 
                        kod_mqf = :kod_mqf, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE id_plo = :id_plo";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $result = $stmt->execute([
                ':keterangan_bm' => $keteranganbm,
                ':kod_mqf'       => $kodmqf,
                ':updated_by'    => $updated_by,
                ':id_plo'        => $idplo
            ]);

            if (!$result) {
                $this->pdoSPK->rollBack();
                return false;
            }

            $sql_delete = "DELETE FROM spk_tpenetapan_peo_plo WHERE id_plo = :id_plo";
            $stmt_delete = $this->pdoSPK->prepare($sql_delete);
            $stmt_delete->execute([':id_plo' => $idplo]);

            if (!empty($peoIds) && is_array($peoIds)) {
                $sql_peo = "INSERT INTO spk_tpenetapan_peo_plo (id_peo, id_plo, created_by, created_date) 
                            VALUES (:id_peo, :id_plo, :created_by, NOW())";
                $stmt_peo = $this->pdoSPK->prepare($sql_peo);

                foreach ($peoIds as $peoId) {
                    $stmt_peo->execute([
                        ':id_peo'     => $peoId,
                        ':id_plo'     => $idplo,
                        ':created_by' => $updated_by 
                    ]);
                }
            }  

            $this->pdoSPK->commit();
            return true;

        } catch (\Exception $e) {
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }
            throw $e;
        }      
    }

    public function deleteDataPlo(array $data): bool 
    {
        $idplo = $data['id_plo'] ?? null;
        $status_PLO = 0; // status delete

        if (!$idplo) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql_delete_plo = "UPDATE spk_tplo SET status_aktif = :status_PLO WHERE id_plo = :id_plo";
            $stmt_plo = $this->pdoSPK->prepare($sql_delete_plo);
            $result_plo = $stmt_plo->execute([
                ':status_PLO' => $status_PLO, 
                ':id_plo' => $idplo
            ]);

            if (!$result_plo) {
                $this->pdoSPK->rollBack();
                return false;
            }

            // Padam pemetaan PEO-PLO
            $sql_delete_mapping = "DELETE FROM spk_tpenetapan_peo_plo WHERE id_plo = :id_plo";
            $stmt_mapping = $this->pdoSPK->prepare($sql_delete_mapping);
            $result_mapping = $stmt_mapping->execute([
                ':id_plo' => $idplo
            ]);

            if (!$result_mapping) {
                $this->pdoSPK->rollBack();
                return false;
            }

            // Jika kedua-duanya berjaya, simpan perubahan
            $this->pdoSPK->commit();
            return true;

        } catch (Exception $e) {
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }
            throw $e;
        }      
    }

    public function salinPloSesi(array $data): bool 
    {
        $program_universiti = 'Program';
        
        $toSesiId      = $data['txtsesiid'] ?? null;          
        $toProgramId   = $data['txtprogramid'] ?? null;     
        $fromSesiId    = $data['selectSesiPLOModal'] ?? null; 
        $fromProgramId = $data['selectProgramPLOModal'] ?? null;
        $createdBy     = $data['created_by'] ?? null;
        $kodJabatan    = $data['ptj'] ?? null;

        if (!$toSesiId || !$fromSesiId || !$toProgramId || !$fromProgramId) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sqlSelectPlo = "SELECT kod_plo, keterangan_bm, kod_mqf 
                            FROM spk_tplo 
                            WHERE status_aktif = 1 
                            AND kod_sesi = :from_sesi 
                            AND kod_program = :from_program 
                            AND program_universiti = :program_uni";

            $stmtSelectPlo = $this->pdoSPK->prepare($sqlSelectPlo);
            $stmtSelectPlo->execute([
                ':from_sesi'    => $fromSesiId,
                ':from_program' => $fromProgramId,
                ':program_uni'  => $program_universiti
            ]);

            $listPlo = $stmtSelectPlo->fetchAll(PDO::FETCH_ASSOC);

            if (empty($listPlo)) {
                $this->pdoSPK->rollBack();
                return false;
            }

            $sqlInsertPlo = "INSERT INTO spk_tplo (
                                program_universiti, kod_plo, keterangan_bm, 
                                kod_sesi, kod_jabatan, kod_program, 
                                kod_mqf, created_by, created_date
                            ) VALUES (
                                :program_uni, :kod_plo, :keterangan_bm, 
                                :kod_sesi, :kod_jabatan, :kod_program, 
                                :kod_mqf, :created_by, NOW()
                            )";
            $stmtInsertPlo = $this->pdoSPK->prepare($sqlInsertPlo);

            foreach ($listPlo as $plo) {
                $stmtInsertPlo->execute([
                    ':program_uni'   => $program_universiti,
                    ':kod_plo'       => $plo['kod_plo'],
                    ':keterangan_bm' => $plo['keterangan_bm'],
                    ':kod_sesi'      => $toSesiId,
                    ':kod_jabatan'   => $kodJabatan,
                    ':kod_program'   => $toProgramId,
                    ':kod_mqf'       => $plo['kod_mqf'] ?? null,
                    ':created_by'    => $createdBy
                ]);
            }

            $this->pdoSPK->commit();
            return true;

        } catch (\Exception $e) {
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }
            throw $e;
        }
    }
}