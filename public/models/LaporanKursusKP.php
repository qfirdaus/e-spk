<?php
declare(strict_types=1);

class LaporanKursusKP {
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

        $sql = "SELECT DISTINCT(sesi2), f005term, semester FROM v005_spk WHERE f005term LIKE :kodTerm ORDER BY sesi2 DESC";
        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute([':kodTerm' => $kodTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseList(string $sesi, string $kategori): array {
        if (empty($sesi) || empty($kategori)) return [];

        $sql = "SELECT id_kursus, kod_kursus, term_pengajian, kategori_kursus, penyelaras_kursus, updated_date 
                FROM spk_tkursus 
                WHERE term_pengajian = :sesi AND program_universiti = :kategori 
                ORDER BY kod_kursus";
        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':kategori' => $kategori]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($courses as &$course) {
            $sqlSubjek = "SELECT subjekbm FROM v270offer_spk WHERE kodk = :kodk AND term = :term";
            $stmtSubjek = $this->dbStudent->prepare($sqlSubjek);
            $stmtSubjek->execute([':kodk' => $course['kod_kursus'], ':term' => $course['term_pengajian']]);
            $subjek = $stmtSubjek->fetch(PDO::FETCH_ASSOC);
            $course['subjekbm'] = $subjek ? $subjek['subjekbm'] : '-';

            $course['gelar_nama'] = '-';
            if (!empty($course['penyelaras_kursus'])) {
                $sqlStaff = "SELECT gelar_nama FROM v630staf_service_skim_all WHERE nopekerja = :nopekerja";
                $stmtStaff = $this->dbStaff->prepare($sqlStaff);
                $stmtStaff->execute([':nopekerja' => $course['penyelaras_kursus']]);
                $staff = $stmtStaff->fetch(PDO::FETCH_ASSOC);
                if ($staff) {
                    $course['gelar_nama'] = $staff['gelar_nama'];
                }
            }
        }
        return $courses;
    }

    // MUAT TURUN EXCEL (TABLE 4)
    public function getExcelData(string $idKursus): ?array {
        $data = [];

        $sql = "SELECT * FROM spk_tkursus WHERE id_kursus = :id";
        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([':id' => $idKursus]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) return null;

        $sqlOffer = "SELECT subjekbm, kredit FROM v270offer_spk WHERE kodk = :kodk AND term = :term";
        $stmtOffer = $this->dbStudent->prepare($sqlOffer);
        $stmtOffer->execute([':kodk' => $course['kod_kursus'], ':term' => $course['term_pengajian']]);
        $offer = $stmtOffer->fetch(PDO::FETCH_ASSOC);
        $course['subjekbm'] = $offer['subjekbm'] ?? '-';
        $course['kredit'] = $offer['kredit'] ?? '-';

        $sqlPrereq = "SELECT f240psyarat1, f240psyarat2, f240psyarat3, f240psyarat4, f240psyarat5 FROM t240kursus WHERE f240kodk = :kodk";
        $stmtPrereq = $this->dbStudent->prepare($sqlPrereq);
        $stmtPrereq->execute([':kodk' => $course['kod_kursus']]);
        $data['prereq'] = $stmtPrereq->fetch(PDO::FETCH_ASSOC);

        $course['gelar_nama'] = '-';
        if (!empty($course['penyelaras_kursus'])) {
            $sqlStaff = "SELECT gelar_nama FROM v630staf_service_skim_all WHERE nopekerja = :nopekerja";
            $stmtStaff = $this->dbStaff->prepare($sqlStaff);
            $stmtStaff->execute([':nopekerja' => $course['penyelaras_kursus']]);
            $staff = $stmtStaff->fetch(PDO::FETCH_ASSOC);
            if ($staff) $course['gelar_nama'] = $staff['gelar_nama'];
        }
        $data['course'] = $course;

        // CLO, PLO, Kaedah Pengajaran & Penilaian
        $stmtClo = $this->dbSPK->prepare("SELECT * FROM spk_tclo WHERE id_kursus = :id AND status_aktif = 1");
        $stmtClo->execute([':id' => $idKursus]);
        $data['clos'] = $stmtClo->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data['clos'] as &$clo) {
            // Senarai PLO
            $s1 = $this->dbSPK->prepare("SELECT st.kod_plo FROM spk_tpenetapan_plo_clo stpp JOIN spk_tplo st ON stpp.id_plo = st.id_plo WHERE id_clo = :idclo");
            $s1->execute([':idclo' => $clo['id_clo']]);
            $clo['plos'] = $s1->fetchAll(PDO::FETCH_COLUMN);

            // Senarai Teaching Methods
            $s2 = $this->dbSPK->prepare("SELECT p.kaedah_pengajaran FROM spk_tpenetapan_clo_kpengajaran cp JOIN spk_tkaedah_pengajaran p ON cp.id_kaedah_pengajaran = p.id_kaedah_pengajaran WHERE id_clo = :idclo");
            $s2->execute([':idclo' => $clo['id_clo']]);
            $clo['methods'] = $s2->fetchAll(PDO::FETCH_COLUMN);

            // Senarai Assessments
            $s3 = $this->dbSPK->prepare("SELECT p.penilaian FROM spk_tpenetapan_clo_penilaian cp JOIN spk_tpenilaian p ON cp.id_penilaian = p.id_penilaian WHERE id_clo = :idclo");
            $s3->execute([':idclo' => $clo['id_clo']]);
            $clo['assessments'] = $s3->fetchAll(PDO::FETCH_COLUMN);
        }

        // Transferable Skills
        $stmtSkill = $this->dbSPK->prepare("SELECT k.kemahiran FROM spk_tpenetapan_kursuskemahiran pk JOIN spk_tkemahiran k ON pk.id_kemahiran = k.id_kemahiran WHERE pk.id_kursus = :id ORDER BY k.id_kemahiran ASC");
        $stmtSkill->execute([':id' => $idKursus]);
        $data['skills'] = $stmtSkill->fetchAll(PDO::FETCH_ASSOC);

        // CCO SLT
        $stmtCco = $this->dbSPK->prepare("SELECT content_outline, c.kod_clo, f2f_lecture, f2f_tutorial, f2f_practical, f2f_others, nf2f_guided, nf2f_independent, slt FROM spk_tslt s LEFT JOIN spk_tclo c ON s.id_clo = c.id_clo WHERE s.status_aktif = 1 AND s.id_kursus = :id");
        $stmtCco->execute([':id' => $idKursus]);
        $data['slt_cco'] = $stmtCco->fetchAll(PDO::FETCH_ASSOC);

        // Continuous Assessment SLT
        $stmtCa = $this->dbSPK->prepare("SELECT svpk.penilaian, percentage, f2f, nf2f, slt FROM spk_vpenilaian_kursus svpk LEFT JOIN spk_tpenetapan_kursuspenilaian stpkp ON svpk.id_kursus = stpkp.id_kursus AND svpk.id_penilaian = stpkp.id_penilaian WHERE svpk.kod_jenispenilaian = 1 AND svpk.id_kursus = :id");
        $stmtCa->execute([':id' => $idKursus]);
        $data['slt_ca'] = $stmtCa->fetchAll(PDO::FETCH_ASSOC);

        // Final Assessment SLT
        $stmtFa = $this->dbSPK->prepare("SELECT svpk.penilaian, percentage, f2f, nf2f, slt FROM spk_vpenilaian_kursus svpk LEFT JOIN spk_tpenetapan_kursuspenilaian stpkp ON svpk.id_kursus = stpkp.id_kursus AND svpk.id_penilaian = stpkp.id_penilaian WHERE svpk.kod_jenispenilaian = 2 AND svpk.id_kursus = :id");
        $stmtFa->execute([':id' => $idKursus]);
        $data['slt_fa'] = $stmtFa->fetchAll(PDO::FETCH_ASSOC);

        // Rujukan / References
        $stmtRef = $this->dbSPK->prepare("SELECT reference_desc FROM spk_vreference_kursus WHERE id_kursus = :id");
        $stmtRef->execute([':id' => $idKursus]);
        $data['references'] = $stmtRef->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }    
}
?>