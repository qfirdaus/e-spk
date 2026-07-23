<?php
declare(strict_types=1);

class MaklumatPEO
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

    // Sesi Kemasukan (v005_spk)
    public function getSesiList(string $tahapPengajian): array
    {
        if (empty($tahapPengajian)) return [];

        $sql = "SELECT distinct(sesi2), LEFT( f005term, LEN( f005term ) -1 ) as term  FROM v005_spk WHERE $tahapPengajian ORDER BY sesi2 DESC";
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
    public function getSelectedTermDetail(string $sesi2, string $tahapPengajian): array
    {
        $sql = "SELECT *, LEFT( f005term, LEN( f005term ) -1 ) as term  FROM v005_spk WHERE sesi2 = :sesi2 AND $tahapPengajian";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':sesi2' => $sesi2]);
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

    //Retrieve PEO List
    public function getPeoList(string $sesi, string $kodProgram): array
    {
        $sql = "SELECT * FROM spk_tpeo 
                WHERE status_aktif = 1 
                AND sesi = :sesi 
                AND kod_program = :kod_program";
                
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([
            ':sesi' => $sesi,
            ':kod_program' => $kodProgram
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPeoBaharu(array $data): bool 
    {
        $sesiid       = $data['txtsesiid'] ?? null;
        $sesi         = $data['txtsesi'] ?? null;
        $kodpeo       = $data['selectkodpeo'] ?? null;
        $programid    = $data['txtprogramid'] ?? null;
        $keteranganbm = $data['txtketeranganpeo'] ?? null;
        
        $tarikhsenat  = $this->convertDateFormat($data['txttarikhsenat'] ?? null); 
        
        $ptj          = $data['txtptj'] ?? null;
        $created_by   = $data['created_by'] ?? null;

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "INSERT INTO spk_tpeo (kod_peo, keterangan_bm, tarikh_senat, kod_sesi, sesi, kod_jabatan, kod_program, created_by, created_date) 
                    VALUES (:kodpeo, :keterangan_bm, :tarikhsenat, :kod_sesi, :sesi, :kod_jabatan, :kod_program, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            
            $stmt->execute([
                ':kodpeo'         => $kodpeo, 
                ':keterangan_bm'  => $keteranganbm,
                ':tarikhsenat'    => $tarikhsenat, 
                ':kod_sesi'       => $sesiid,
                ':sesi'           => $sesi,
                ':kod_jabatan'    => $ptj,   
                ':kod_program'    => $programid,
                ':created_by'     => $created_by
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

    public function updateDataPeo(array $data): bool 
    {
        $idpeo        = $data['txtidplotxtidpeo_edit'] ?? null;
        $keteranganbm = $data['txtketeranganplo'] ?? null;
        $kodmqf       = $data['selectkodmqf_edit'] ?? null;
        $updated_by   = $data['updated_by'] ?? null;
        $chkpeo       = $data['chkpeo'] ?? [];        

        if (!$idpeo) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tplo 
                    SET keterangan_bm = :keterangan_bm, 
                        kod_mqf = :kod_mqf, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE idpeo = :idpeo";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $result = $stmt->execute([
                ':keterangan_bm' => $keteranganbm,
                ':kod_mqf'       => $kodmqf,
                ':updated_by'    => $updated_by,
                ':idpeo'        => $idpeo
            ]);

            if (!$result) {
                $this->pdoSPK->rollBack();
                return false;
            }

            // $sql_delete = "DELETE FROM spk_tpenetapan_peo_plo WHERE id_plo = :id_plo";
            // $stmt_delete = $this->pdoSPK->prepare($sql_delete);
            // $stmt_delete->execute([':id_plo' => $idplo]);

            // if (!empty($chkpeo)) {
            //     $sql_peo = "INSERT INTO spk_tpenetapan_peo_plo (id_peo, id_plo, created_by, created_date) 
            //                 VALUES (:id_peo, :id_plo, :created_by, NOW())";
            //     $stmt_peo = $this->pdoSPK->prepare($sql_peo);

            //     foreach ($chkpeo as $peo) {
            //         $stmt_peo->execute([
            //             ':id_peo'     => $peo,
            //             ':id_plo'     => $idplo,
            //             ':created_by' => $updated_by 
            //         ]);
            //     }
            // }  

            $this->pdoSPK->commit();
            return true;

        } catch (Exception $e) {
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }
            throw $e;
        }      
    }

    private function convertDateFormat(?string $dateStr, string $inputFormat = 'd-m-Y', string $outputFormat = 'Y-m-d'): ?string
    {
        if (empty($dateStr) || empty(trim($dateStr))) {
            return null;
        }

        $dateObj = DateTime::createFromFormat($inputFormat, trim($dateStr));
        
        if ($dateObj && $dateObj->format($inputFormat) === trim($dateStr)) {
            return $dateObj->format($outputFormat);
        }

        return null;
    }   
}