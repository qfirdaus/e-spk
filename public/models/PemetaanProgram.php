<?php
declare(strict_types=1);

class PemetaanProgram {
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

    // Kira Statistik PEO
    public function getPeoStats(string $sesi, string $program): array {
        if (empty($sesi) || empty($program)) return [];
        $sesiPrefix = substr($sesi, 0, -1) . "%";

        $sql = "SELECT id_peo, kod_peo FROM spk_tpeo WHERE status_aktif = 1 AND kod_sesi LIKE :sesiPrefix AND kod_program = :program ORDER BY kod_peo";
        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([':sesiPrefix' => $sesiPrefix, ':program' => $program]);
        $peos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlMap = "SELECT id_peo, COUNT(*) as count_peo FROM v_spk_penetapan_peo_plo WHERE kod_sesi LIKE :sesiPrefix AND kod_program = :program GROUP BY id_peo";
        $stmtMap = $this->dbSPK->prepare($sqlMap);
        $stmtMap->execute([':sesiPrefix' => $sesiPrefix, ':program' => $program]);
        $mapCounts = $stmtMap->fetchAll(PDO::FETCH_ASSOC);

        // Pengiraan Peratusan (Total sum)
        $totalAllMappings = array_sum(array_column($mapCounts, 'count_peo'));

        foreach ($peos as &$peo) {
            $peo['total'] = 0;
            $peo['percentage'] = 0;
            foreach ($mapCounts as $map) {
                if ($map['id_peo'] == $peo['id_peo']) {
                    $peo['total'] = (int)$map['count_peo'];
                    $peo['percentage'] = $totalAllMappings > 0 ? round(($peo['total'] / $totalAllMappings) * 100, 2) : 0;
                    break;
                }
            }
        }
        return $peos;
    }

    // Kira Statistik PLO
    public function getPloStats(string $sesi, string $program): array {
        if (empty($sesi) || empty($program)) return [];

        $sql = "SELECT id_plo, kod_plo FROM spk_tplo WHERE status_aktif = 1 AND kod_sesi = :sesi AND kod_program = :program ORDER BY CAST(SUBSTRING(kod_plo, 4) AS UNSIGNED) ASC";
        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':program' => $program]);
        $plos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlMap = "SELECT id_plo, COUNT(id_plo) as count_plo FROM v_spk_penetapan_kursus_plo WHERE term_pengajian = :sesi AND kod_program = :program GROUP BY id_plo";
        $stmtMap = $this->dbSPK->prepare($sqlMap);
        $stmtMap->execute([':sesi' => $sesi, ':program' => $program]);
        $mapCounts = $stmtMap->fetchAll(PDO::FETCH_ASSOC);

        $totalAllMappings = array_sum(array_column($mapCounts, 'count_plo'));

        foreach ($plos as &$plo) {
            $plo['total'] = 0;
            $plo['percentage'] = 0;
            foreach ($mapCounts as $map) {
                if ($map['id_plo'] == $plo['id_plo']) {
                    $plo['total'] = (int)$map['count_plo'];
                    $plo['percentage'] = $totalAllMappings > 0 ? round(($plo['total'] / $totalAllMappings) * 100, 2) : 0;
                    break;
                }
            }
        }
        return $plos;
    }
}
?>