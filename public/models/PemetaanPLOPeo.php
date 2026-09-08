<?php
declare(strict_types=1);

class PemetaanPLOPeo {
    private PDO $dbSPK;
    private PDO $dbStudent;
    private PDO $dbStaff;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent, PDO $pdoStaff) {
        $this->dbSPK = $pdoSPK;
        $this->dbStudent = $pdoStudent;
        $this->dbStaff = $pdoStaff;
    }

    public function getTermList(string $pengajian): array {
        if (empty($pengajian)) return [];
        
        $kodTerm = "";
        if ($pengajian === "Asasi") $kodTerm = "B%";
        elseif ($pengajian === "Diploma") $kodTerm = "E%";
        elseif ($pengajian === "Sarjana Muda") $kodTerm = "A%";

        if (empty($kodTerm)) return [];

        $sql = "SELECT DISTINCT(sesi2), f005term, semester FROM v005_spk WHERE f005term LIKE :kodTerm ORDER BY f005term DESC";
        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute([':kodTerm' => $kodTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getKodJabatanStaf(string $stafID): string
    {
        $sql = "SELECT kdjbtnhakiki as f_jabatanKod FROM v630staf_service_skim_all WHERE nopekerja = :stafID";
        $stmt = $this->dbStaff->prepare($sql);
        $stmt->execute([':stafID' => $stafID]);
        $ptj = $stmt->fetchColumn();
        
        return $ptj ? (string)$ptj : '';
    }       

    public function getProgramList(string $pengajian, string $ptj): array {
        if (empty($pengajian) || empty($ptj)) return [];

        $sql = "SELECT id_program, program FROM v006_spk WHERE tahap_pengajian = :pengajian AND kdjbt = :ptj ORDER BY program";
        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute([':pengajian' => $pengajian, ':ptj' => $ptj]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPEOList(string $sesi, string $program): array {
        if (empty($sesi) || empty($program)) return [];

        $sesiPrefix = substr($sesi, 0, -1) . "%";

        $sql = "SELECT id_peo, kod_peo FROM spk_tpeo WHERE status_aktif = 1 AND kod_sesi LIKE :sesiPrefix AND kod_program = :program ORDER BY kod_peo";
        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([':sesiPrefix' => $sesiPrefix, ':program' => $program]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPemetaanData(string $sesi, string $program): array {
        if (empty($sesi) || empty($program)) return [];

        $sqlPlo = "SELECT id_plo, kod_plo, keterangan_bm 
                   FROM spk_tplo 
                   WHERE status_aktif = 1 AND kod_sesi = :sesi AND kod_program = :program 
                   ORDER BY CAST(SUBSTRING(kod_plo, 4) AS UNSIGNED) ASC";
        $stmtP = $this->dbSPK->prepare($sqlPlo);
        $stmtP->execute([':sesi' => $sesi, ':program' => $program]);
        $plos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        $sqlMap = "SELECT id_plo, id_peo FROM v_spk_penetapan_peo_plo WHERE kod_sesi = :sesi AND kod_program = :program";
        $stmtM = $this->dbSPK->prepare($sqlMap);
        $stmtM->execute([':sesi' => $sesi, ':program' => $program]);
        $mappings = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        foreach ($plos as &$p) {
            $p['peos'] = [];
            foreach ($mappings as $map) {
                if ((string)$map['id_plo'] === (string)$p['id_plo']) {
                    $p['peos'][] = $map['id_peo'];
                }
            }
        }
        return $plos;
    }
}
?>