<?php
declare(strict_types=1);

class LaporanPLOPK {
    private PDO $dbSPK;
    private PDO $dbStudent;
    private PDO $dbStaff;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent, PDO $pdoStaff) {
        $this->dbSPK = $pdoSPK;
        $this->dbStudent = $pdoStudent;
        $this->dbStaff = $pdoStaff;
    }

    public function getKodJabatanStaf(string $stafID): string
    {
        $sql = "SELECT kdjbtnhakiki as f_jabatanKod FROM v630staf_service_skim_all WHERE nopekerja = :stafID";
        $stmt = $this->dbStaff->prepare($sql);
        $stmt->execute([':stafID' => $stafID]);
        $ptj = $stmt->fetchColumn();
        
        return $ptj ? (string)$ptj : '';
    }    

    public function getTermList(string $pengajian): array {
        if (empty($pengajian)) return [];
        
        $kodTerm = "";
        if ($pengajian === "Asasi") $kodTerm = "B%";
        elseif ($pengajian === "Diploma") $kodTerm = "E%";
        elseif ($pengajian === "Sarjana Muda") $kodTerm = "A%";

        if (empty($kodTerm)) return [];

        $sql = "SELECT * FROM v005_spk WHERE f005term LIKE :kodTerm ORDER BY f005term DESC";
        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute([':kodTerm' => $kodTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProgramList(string $pengajian, string $ptj): array {
        if (empty($pengajian) || empty($ptj)) return [];

        $sql = "SELECT * FROM v006_spk WHERE tahap_pengajian = :pengajian AND kdjbt = :ptj ORDER BY program";
        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute([':pengajian' => $pengajian, ':ptj' => $ptj]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPLOList(string $sesi, string $program): array {
        if (empty($sesi) || empty($program)) return [];

        $sql = "SELECT * FROM spk_tplo WHERE status_aktif = 1 AND kod_sesi = :sesi AND kod_program = :program";
        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':program' => $program]);
        $ploList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ploList as &$plo) {
            
            $sqlPeo = "SELECT st.kod_peo, st.keterangan_bm 
                       FROM spk_tpenetapan_peo_plo spp
                       JOIN spk_tpeo st ON spp.id_peo = st.id_peo
                       WHERE spp.id_plo = :id_plo";
            $stmtPeo = $this->dbSPK->prepare($sqlPeo);
            $stmtPeo->execute([':id_plo' => $plo['id_plo']]);
            $plo['senarai_peo'] = $stmtPeo->fetchAll(PDO::FETCH_ASSOC);

            $sqlClo = "SELECT sc.kod_clo, sc.keterangan_bm 
                       FROM spk_tpenetapan_plo_clo spc
                       JOIN spk_tclo sc ON spc.id_clo = sc.id_clo
                       WHERE spc.id_plo = :id_plo AND sc.status_aktif = 1";
            $stmtClo = $this->dbSPK->prepare($sqlClo);
            $stmtClo->execute([':id_plo' => $plo['id_plo']]);
            $plo['senarai_clo'] = $stmtClo->fetchAll(PDO::FETCH_ASSOC);
            
        }
        return $ploList;      
    }
}
?>