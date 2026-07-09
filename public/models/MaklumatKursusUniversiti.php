<?php
declare(strict_types=1);

class MaklumatKursusUniversiti
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
    }
    public function getSesiList(string $tahapPengajian): array
    {
        if (empty($tahapPengajian)) return [];
        
        $sql = "SELECT * FROM v005_spk WHERE $tahapPengajian ORDER BY f005term DESC";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** semakan kursus dan kemasukan */
    public function getKursusDitawarkan(string $sesiKursus, string $createdBy): array
    {
        $senaraiAll = []; 

        // senarai subjek ditawarkan
        $sql = "SELECT DISTINCT(kodk) as kod_kursus, subjekbm, subjekbi FROM v270offer_spk WHERE term = :term ORDER BY kodk";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':term' => $sesiKursus]);
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subjects as $result) {
            $sqlCheck = "SELECT kod_kursus, term_pengajian FROM spk_tkursus WHERE kod_kursus = :kod AND term_pengajian = :term";
            $stmtCheck = $this->pdoSPK->prepare($sqlCheck);
            $stmtCheck->execute([
                ':kod' => $result["kod_kursus"],
                ':term' => $sesiKursus
            ]);

            $wujud = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            $result['wujud_dalam_spk'] = $wujud ? true : false; 

            $senaraiAll[] = $result;
        }

        return $senaraiAll; 
    }

    public function getSenaraiKursusTelahDaftar(string $sesiKursus, string $programUniversiti): array
    {
        $senaraiAkhir = [];

        $sqlSPK = "SELECT DISTINCT id_kursus, kod_kursus, term_pengajian, kategori_kursus, penyelaras_kursus 
                   FROM spk_tkursus 
                   WHERE term_pengajian = :term 
                   AND program_universiti = :program 
                   ORDER BY kod_kursus";
                   
        $stmtSPK = $this->pdoSPK->prepare($sqlSPK);
        $stmtSPK->execute([
            ':term'    => $sesiKursus,
            ':program' => $programUniversiti
        ]);
        $senaraiKursus = $stmtSPK->fetchAll(PDO::FETCH_ASSOC);

        $sqlPenyelaras = "SELECT gelar_nama FROM stafdb.dbo.v630staf_service_skim_aktif WHERE nopekerja = :nopekerja";
        $stmtPenyelaras = $this->pdoStudent->prepare($sqlPenyelaras);

        $sqlPensyarah = "SELECT DISTINCT(nopekerja), gelar_nama 
                         FROM v270offer_spk a 
                         LEFT JOIN stafdb.dbo.v630staf_service_skim_aktif s ON a.stafno = CONVERT(varchar(10), s.idpekerja) 
                         WHERE kodk = :kod AND term = :term";
        $stmtPensyarah = $this->pdoStudent->prepare($sqlPensyarah);

        $sqlSubjek = "SELECT subjekbm FROM v270offer_spk WHERE term = :term AND kodk = :kod";
        $stmtSubjek = $this->pdoStudent->prepare($sqlSubjek);

        foreach ($senaraiKursus as $row) {
            
            $row['penyelaras_nama'] = '';
            if (!empty($row['penyelaras_kursus'])) {
                $stmtPenyelaras->execute([':nopekerja' => $row['penyelaras_kursus']]);
                $pData = $stmtPenyelaras->fetch(PDO::FETCH_ASSOC);
                $row['penyelaras_nama'] = $pData ? trim($pData['gelar_nama']) : '';
            }

            $stmtPensyarah->execute([
                ':kod'  => $row['kod_kursus'], 
                ':term' => $row['term_pengajian']
            ]);
            $row['senarai_pensyarah'] = $stmtPensyarah->fetchAll(PDO::FETCH_ASSOC);

            $stmtSubjek->execute([
                ':term' => $row["term_pengajian"],
                ':kod'  => $row["kod_kursus"]
            ]);
            $subjek = $stmtSubjek->fetch(PDO::FETCH_ASSOC);
            $row['subjekbm'] = $subjek ? trim($subjek['subjekbm']) : '';

            $senaraiAkhir[] = $row;
        }

        return $senaraiAkhir;
    }

    public function updateKategoriKursus(int $idKursus, string $kategori, string $updatedBy): bool
    {
        $sql = "UPDATE spk_tkursus 
                SET kategori_kursus = :kategori, 
                    updated_by = :updated_by, 
                    updated_date = NOW() 
                WHERE id_kursus = :id_kursus";
                
        $stmt = $this->pdoSPK->prepare($sql);
        
        return $stmt->execute([
            ':kategori'    => $kategori,
            ':updated_by'  => $updatedBy,
            ':id_kursus'   => $idKursus
        ]);
    }

    //?string : penyelaras value in string or null
    public function updatePenyelarasKursus(int $idKursus, ?string $penyelaras, string $updatedBy): bool
    {
        $sql = "UPDATE spk_tkursus 
                SET penyelaras_kursus = :penyelaras, updated_by = :updated_by, updated_date = NOW() 
                WHERE id_kursus = :id_kursus";
        return $this->pdoSPK->prepare($sql)->execute([
            ':penyelaras' => $penyelaras, 
            ':updated_by' => $updatedBy,
            ':id_kursus'  => $idKursus
        ]);
    }    

    // Sesi Kursus
    public function getSelectedTermDetail(string $sesiKursus): array
    {
        $sql = "SELECT * FROM v005_spk WHERE f005term = :term";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':term' => $sesiKursus]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // Retrieve All Subjects by Term dari v270offer_spk
    public function getAllSubjectByTerm(string $sesiKursus): array
    {
        $sql = "SELECT DISTINCT(kodk), subjekbm, subjekbi FROM v270offer_spk WHERE term = :term ORDER BY kodk";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':term' => $sesiKursus]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // simpan Kursus baru
    public function addKursusBaharu(array $data): bool 
    {
        $program_universiti = 'Universiti';
        
        $sesiid = $data['txtsesiid'] ?? null;
        $kursus = $data['selectkursus'] ?? null;
        $kategorikursus = $data['selectKategoriKursus'] ?? null;
        $created_by = $data['created_by'] ?? null;

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "INSERT INTO spk_tkursus(program_universiti, kod_kursus, term_pengajian, kategori_kursus, created_by, created_date)
                    VALUES (:program_uni, :kod_kursus, :kod_sesi, :kategori_kursus, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            
            $stmt->execute([
                ':program_uni'     => $program_universiti,
                ':kod_kursus'      => $kursus,
                ':kod_sesi'        => $sesiid,
                ':kategori_kursus' => $kategorikursus,
                ':created_by'      => $created_by
            ]);

            $this->pdoSPK->commit();
            return true;

        } catch (\Throwable $e) { 
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }
            throw $e; 
        }
    }  
}