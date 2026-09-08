<?php
declare(strict_types=1);

class PemetaanKursusPLO {
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

    public function getPLOList(string $sesi, string $program): array {
        if (empty($sesi) || empty($program)) return [];

        $sql = "SELECT id_plo, kod_plo FROM spk_tplo WHERE status_aktif = 1 AND kod_sesi = :sesi AND kod_program = :program AND program_universiti = 'Program' ORDER BY kod_plo";
        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':program' => $program]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPemetaanData(string $sesi, string $program, string $ptj): array {
        if (empty($sesi) || empty($program) || empty($ptj)) return [];

        $sqlKursus = "SELECT id_kursus, kod_kursus, term_pengajian 
                      FROM spk_tkursus 
                      WHERE term_pengajian = :sesi AND kod_jabatan = :ptj AND kod_program = :program AND program_universiti = 'Program' AND penyelaras_kursus IS NOT NULL 
                      ORDER BY kod_kursus";
        $stmtK = $this->dbSPK->prepare($sqlKursus);
        $stmtK->execute([':sesi' => $sesi, ':ptj' => $ptj, ':program' => $program]);
        $courses = $stmtK->fetchAll(PDO::FETCH_ASSOC);

        $sqlMap = "SELECT id_kursus, id_plo FROM v_spk_penetapan_kursus_plo WHERE term_pengajian = :sesi AND kod_program = :program";
        $stmtM = $this->dbSPK->prepare($sqlMap);
        $stmtM->execute([':sesi' => $sesi, ':program' => $program]);
        $mappings = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        foreach ($courses as &$c) {
            $sqlSub = "SELECT subjekbm FROM v270offer_spk WHERE kodk = :kodk AND term = :term";
            $stmtSub = $this->dbStudent->prepare($sqlSub);
            $stmtSub->execute([':kodk' => $c['kod_kursus'], ':term' => $c['term_pengajian']]);
            $subData = $stmtSub->fetch(PDO::FETCH_ASSOC);
            $c['subjekbm'] = $subData ? $subData['subjekbm'] : '-';

            $c['plos'] = [];
            foreach ($mappings as $map) {
                if ($map['id_kursus'] == $c['id_kursus']) {
                    $c['plos'][] = $map['id_plo'];
                }
            }
        }
        return $courses;
    }
}
?>