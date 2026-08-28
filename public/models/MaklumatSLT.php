<?php
class MaklumatSLT {
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private PDO $pdoStaff;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent, PDO $pdoStaff)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
        $this->pdoStaff = $pdoStaff;
    }

    public function getTermList($pengajian) {
        $kodTerm = "";
        if ($pengajian == "Asasi") $kodTerm = "f005term LIKE 'B%'";
        else if ($pengajian == "Diploma") $kodTerm = "f005term LIKE 'E%'";
        else if ($pengajian == "Sarjana Muda") $kodTerm = "f005term LIKE 'A%'";
        
        if (empty($kodTerm)) return [];

        $sql = "SELECT DISTINCT sesi2, f005term, semester FROM v005_spk WHERE $kodTerm ORDER BY sesi2 DESC";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseList($sesi, $idStaf) {
        $sql = "SELECT id_kursus, kod_kursus, term_pengajian FROM spk_tkursus 
                WHERE status_aktif = 1 AND term_pengajian = :sesi AND penyelaras_kursus = :idStaf";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':idStaf' => $idStaf]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($courses as &$c) {
            $sql2 = "SELECT subjekbm FROM v270offer_spk WHERE kodk = :kodk AND term = :term";
            $stmt2 = $this->pdoStudent->prepare($sql2);
            $stmt2->execute([':kodk' => $c['kod_kursus'], ':term' => $c['term_pengajian']]);
            $offer = $stmt2->fetch(PDO::FETCH_ASSOC);
            $c['subjekbm'] = $offer ? $offer['subjekbm'] : 'N/A';
        }
        return $courses;
    }

    public function getCLOList($kursusId) {
        $sql = "SELECT id_clo, kod_clo FROM spk_tclo WHERE id_kursus = :kursusId ORDER BY kod_clo ASC";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSLTList($kursusId, $sesi) {
        $sql = "SELECT s.*, c.kod_clo 
                FROM spk_tslt s 
                LEFT JOIN spk_tclo c ON s.id_clo = c.id_clo 
                WHERE s.status_aktif = 1 AND s.id_kursus = :kursusId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tambahSLT($data) {
        $sql = "INSERT INTO spk_tslt (content_outline, id_clo, f2f_lecture, f2f_tutorial, f2f_practical, f2f_others, nf2f_guided, nf2f_independent, slt, id_kursus, created_by, created_date) 
                VALUES (:content, :idclo, :lec, :tut, :prac, :oth, :nfg, :nfi, :slt, :kursusid, :created_by, NOW())";
        $stmt = $this->pdoSPK->prepare($sql);
        return $stmt->execute($data);
    }

    public function kemaskiniSLT($data) {
        $sql = "UPDATE spk_tslt SET content_outline = :content, id_clo = :idclo, f2f_lecture = :lec, f2f_tutorial = :tut, 
                f2f_practical = :prac, f2f_others = :oth, nf2f_guided = :nfg, nf2f_independent = :nfi, slt = :slt, 
                updated_by = :updated_by, updated_date = NOW() 
                WHERE id_slt = :idslt AND id_kursus = :kursusid";
        $stmt = $this->pdoSPK->prepare($sql);
        return $stmt->execute($data);
    }

    public function hapusSLT($idslt, $deleted_by) {
        $sql = "UPDATE spk_tslt SET status_aktif = 0, deleted_by = :deleted_by, deleted_date = NOW() WHERE id_slt = :idslt";
        $stmt = $this->pdoSPK->prepare($sql);
        return $stmt->execute([':idslt' => $idslt, ':deleted_by' => $deleted_by]);
    }
}
?>