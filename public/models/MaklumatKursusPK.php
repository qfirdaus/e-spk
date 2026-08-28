<?php
declare(strict_types=1);

class MaklumatKursusPK
{
    private PDO $pdoSPK;
    private PDO $pdoStaff;
    private PDO $pdoStudent;

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

    public function getSesiList(string $kodTerm): array
    {
        $sql = "SELECT DISTINCT sesi2, f005term, semester FROM v005_spk WHERE $kodTerm ORDER BY sesi2 DESC";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // List Penilaian 
    public function getPenilaianList(int $kursusId, string $term): array
    {
        $sql = "
            SELECT 
                svpk.id_penilaian AS id, 
                svpk.penilaian, 
                svpk.kod_jenispenilaian AS jenis, 
                COALESCE(stpkp.percentage, 0) AS percentage, 
                COALESCE(stpkp.f2f, 0) AS f2f, 
                COALESCE(stpkp.nf2f, 0) AS nf2f
            FROM spk_vpenilaian_kursus svpk 
            LEFT JOIN spk_tpenetapan_kursuspenilaian stpkp 
                   ON svpk.id_kursus = stpkp.id_kursus 
                  AND svpk.id_penilaian = stpkp.id_penilaian
            WHERE svpk.id_kursus = :kursusId 
              AND svpk.term_pengajian = :term
        ";

        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId, ':term' => $term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // List Rujukan
    public function getRujukanList(int $kursusId, string $term): array
    {
        $sql = "
            SELECT 
                id_reference AS id, 
                reference_desc AS reference 
            FROM spk_vreference_kursus 
            WHERE id_kursus = :kursusId 
              AND term_pengajian = :term
        ";

        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId, ':term' => $term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
    // List Kemahiran 
    public function getSenaraiKemahiran(int $kursusId): array
    {
        $sql = "
            SELECT 
                k.id_kemahiran, 
                k.kemahiran,
                IF(pk.id_kursus IS NOT NULL, 1, 0) AS is_selected
            FROM spk_tkemahiran k
            LEFT JOIN spk_tpenetapan_kursuskemahiran pk 
                ON k.id_kemahiran = pk.id_kemahiran 
                AND pk.id_kursus = :kursusId
            WHERE k.status_aktif = 1
            ORDER BY k.kemahiran ASC
        ";

        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseExcelInfo(int $kursusId)
    {
        $sql1 = "SELECT kod_kursus, id_kursus, term_pengajian, sem_pengajian, 
                        tahun_pengajian, kategori_kursus, penyelaras_kursus, 
                        sinopsis_bm, updated_date, special_requirement, other_information 
                 FROM spk_tkursus 
                 WHERE id_kursus = :kursusId";
                 
        $stmt1 = $this->pdoSPK->prepare($sql1);
        $stmt1->execute([':kursusId' => $kursusId]);
        $courseData = $stmt1->fetch(PDO::FETCH_ASSOC);

        if (!$courseData) {
            return [];
        }

        $courseData['subjekbm'] = '';
        $courseData['kredit'] = '';
        $courseData['gelar_nama'] = '';

        if (!empty($courseData['kod_kursus']) && !empty($courseData['term_pengajian'])) {
            $sql2 = "SELECT subjekbm, kredit FROM v270offer_spk WHERE kodk = :kodk AND term = :term";
            $stmt2 = $this->pdoStudent->prepare($sql2);
            $stmt2->execute([
                ':kodk' => $courseData['kod_kursus'],
                ':term' => $courseData['term_pengajian']
            ]);
            
            $offerData = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($offerData) {
                $courseData['subjekbm'] = $offerData['subjekbm'] ?? '';
                $courseData['kredit'] = $offerData['kredit'] ?? '';
            }
        }

        if (!empty($courseData['penyelaras_kursus'])) {
            $sql3 = "SELECT gelar_nama FROM ehrmdb.dbo.v630staf_service_skim_aktif WHERE nopekerja = :nopekerja";
            $stmt3 = $this->pdoStudent->prepare($sql3);
            $stmt3->execute([':nopekerja' => $courseData['penyelaras_kursus']]);
            
            $staffData = $stmt3->fetch(PDO::FETCH_ASSOC);
            if ($staffData) {
                $courseData['gelar_nama'] = $staffData['gelar_nama'] ?? '';
            }
        }

        return $courseData;
    }

    public function getCourseExcelDetails(string $kodk)
    {
        // Dapatkan prasyarat
        $sql = "SELECT * FROM t240kursus WHERE f240kodk = :kodk";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':kodk' => $kodk]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCourseCLOList(int $kursusId)
    {
        $sql = "SELECT * FROM spk_tclo WHERE id_kursus = :kursusId ORDER BY kod_clo ASC";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExcelSkills(int $kursusId)
    {
        $sql = "SELECT * FROM spk_tpenetapan_kursuskemahiran pk 
                JOIN spk_tkemahiran k ON pk.id_kemahiran = k.id_kemahiran
                WHERE k.status_aktif = 1 AND pk.id_kursus = :kursusId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExcelCCO(int $kursusId)
    {
        $sql = "SELECT spk_tslt.id_slt, spk_tslt.content_outline, spk_tslt.id_clo, 
                       spk_tclo.kod_clo, spk_tslt.id_kursus, spk_tslt.f2f_lecture, 
                       spk_tslt.f2f_tutorial, spk_tslt.f2f_practical, spk_tslt.f2f_others, 
                       spk_tslt.nf2f_guided, spk_tslt.nf2f_independent, spk_tslt.slt 
                FROM spk_tslt 
                LEFT JOIN spk_tclo ON spk_tslt.id_clo = spk_tclo.id_clo
                WHERE spk_tslt.id_kursus = :kursusId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExcelAssessment(int $kursusId, int $jenis)
    {
        $sql = "SELECT svpk.id_penilaian, svpk.penilaian, svpk.kod_jenispenilaian, 
                       stpkp.percentage, stpkp.f2f, stpkp.nf2f, stpkp.slt
                FROM spk_vpenilaian_kursus svpk
                LEFT JOIN spk_tpenetapan_kursuspenilaian stpkp 
                       ON svpk.id_kursus = stpkp.id_kursus AND svpk.id_penilaian = stpkp.id_penilaian
                WHERE svpk.kod_jenispenilaian = :jenis AND svpk.id_kursus = :kursusId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':jenis' => $jenis, ':kursusId' => $kursusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExcelReferences(int $kursusId)
    {
        $sql = "SELECT svr.id_reference, svr.term_pengajian, svr.reference_desc 
                FROM spk_vreference_kursus svr 
                WHERE svr.id_kursus = :kursusId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':kursusId' => $kursusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CLO Mapping
    public function getCloPloMapping(int $cloId)
    {
        $sql = "SELECT st.kod_plo FROM spk_tpenetapan_plo_clo stpp 
                JOIN spk_tplo st ON stpp.id_plo = st.id_plo WHERE stpp.id_clo = :cloId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':cloId' => $cloId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN); 
    }

    public function getCloTeachingMethods(int $cloId)
    {
        $sql = "SELECT p.kaedah_pengajaran FROM spk_tpenetapan_clo_kpengajaran cp 
                JOIN spk_tkaedah_pengajaran p ON cp.id_kaedah_pengajaran = p.id_kaedah_pengajaran
                WHERE cp.id_clo = :cloId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':cloId' => $cloId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN); 
    }

    public function getCloAssessments(int $cloId)
    {
        $sql = "SELECT p.penilaian FROM spk_tpenetapan_clo_penilaian cp 
                JOIN spk_tpenilaian p ON cp.id_penilaian = p.id_penilaian
                WHERE cp.id_clo = :cloId";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':cloId' => $cloId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }    

    public function getKursusList(string $sesi, string $stafID): array
    {
        $sqlMySQL = "
            SELECT 
                k.id_kursus, 
                k.kod_kursus, 
                k.sinopsis_bm, 
                k.kategori_kursus, 
                k.term_pengajian,
                k.sem_pengajian,
                k.tahun_pengajian,
                k.special_requirement, 
                k.other_information,

                -- 1. Senarai CLO
                (SELECT GROUP_CONCAT(kod_clo SEPARATOR ', ') 
                 FROM spk_tclo 
                 WHERE id_kursus = k.id_kursus AND status_aktif = 1) AS senarai_clo_string,

                -- Kemahiran
                (SELECT GROUP_CONCAT(km.kemahiran SEPARATOR '\n') 
                 FROM spk_tpenetapan_kursuskemahiran pk 
                 JOIN spk_tkemahiran km ON pk.id_kemahiran = km.id_kemahiran 
                 WHERE pk.id_kursus = k.id_kursus) AS senarai_kemahiran_string,

                -- Penilaian Berterusan 
                (SELECT GROUP_CONCAT(CONCAT(svpk.penilaian, ' (Percentage: ', COALESCE(stpkp.percentage, 0), '%, F2F: ', COALESCE(stpkp.f2f, 0), ', NF2F: ', COALESCE(stpkp.nf2f, 0), ', SLT: ', COALESCE(stpkp.slt, 0), ')') SEPARATOR '\n\n')
                 FROM spk_vpenilaian_kursus svpk
                 LEFT JOIN spk_tpenetapan_kursuspenilaian stpkp ON svpk.id_kursus = stpkp.id_kursus AND svpk.id_penilaian = stpkp.id_penilaian
                 WHERE svpk.kod_jenispenilaian = 1 AND svpk.id_kursus = k.id_kursus AND svpk.term_pengajian = k.term_pengajian) AS senarai_continuous_string,

                -- Penilaian Akhir 
                (SELECT GROUP_CONCAT(CONCAT(svpk.penilaian, ' (Percentage: ', COALESCE(stpkp.percentage, 0), '%, F2F: ', COALESCE(stpkp.f2f, 0), ', NF2F: ', COALESCE(stpkp.nf2f, 0), ', SLT: ', COALESCE(stpkp.slt, 0), ')') SEPARATOR '\n\n')
                 FROM spk_vpenilaian_kursus svpk
                 LEFT JOIN spk_tpenetapan_kursuspenilaian stpkp ON svpk.id_kursus = stpkp.id_kursus AND svpk.id_penilaian = stpkp.id_penilaian
                 WHERE svpk.kod_jenispenilaian = 2 AND svpk.id_kursus = k.id_kursus AND svpk.term_pengajian = k.term_pengajian) AS senarai_final_string,

                -- Rujukan
                (SELECT GROUP_CONCAT(CONCAT('- ', reference_desc) SEPARATOR '\n') 
                 FROM spk_vreference_kursus 
                 WHERE id_kursus = k.id_kursus AND term_pengajian = k.term_pengajian) AS senarai_rujukan_string

            FROM spk_tkursus k
            WHERE k.status_aktif = 1 
            AND k.term_pengajian = :sesi 
            AND k.penyelaras_kursus = :stafID
        ";

        $stmtMySQL = $this->pdoSPK->prepare($sqlMySQL);
        $stmtMySQL->execute([
            ':sesi'   => $sesi, 
            ':stafID' => $stafID
        ]);
        
        $senaraiKursus = $stmtMySQL->fetchAll(PDO::FETCH_ASSOC);

        if (empty($senaraiKursus)) {
            return [];
        }

        $sqlSybase = "SELECT subjekbm FROM v270offer_spk WHERE kodk = :kodk AND term = :term";
        $stmtSybase = $this->pdoStudent->prepare($sqlSybase);

        foreach ($senaraiKursus as &$kursus) {
            $stmtSybase->execute([
                ':kodk' => $kursus['kod_kursus'],
                ':term' => $kursus['term_pengajian']
            ]);
            
            $offer = $stmtSybase->fetch(PDO::FETCH_ASSOC);
            $kursus['subjekbm'] = $offer ? $offer['subjekbm'] : 'N/A'; 
        }

        return $senaraiKursus;
    }

    public function updateKursus(array $formData, array $kemahiranArr, array $penilaianArr, array $rujukanArr, string $stafID): bool
    {
        try {
            $this->pdoSPK->beginTransaction();
            $idKursus = (int)$formData['txtkursusid'];

            $sql1 = "UPDATE spk_tkursus 
                     SET sinopsis_bm = :sinopsis, 
                         sem_pengajian = :sem, 
                         tahun_pengajian = :tahun, 
                         updated_by = :stafID, 
                         updated_date = NOW(), 
                         special_requirement = :req, 
                         other_information = :other 
                     WHERE id_kursus = :idKursus";
            $stmt1 = $this->pdoSPK->prepare($sql1);
            $stmt1->execute([
                ':sinopsis' => $formData['txtsinopsis'],
                ':sem'      => $formData['txtsem'],
                ':tahun'    => $formData['txttahun'],
                ':stafID'   => $stafID,
                ':req'      => $formData['txtrequirement'],
                ':other'    => $formData['txtotherinfo'],
                ':idKursus' => $idKursus
            ]);

            $this->pdoSPK->prepare("DELETE FROM spk_tpenetapan_kursuskemahiran WHERE id_kursus = :idKursus")->execute([':idKursus' => $idKursus]);
            
            if (!empty($kemahiranArr)) {
                $sqlKem = "INSERT INTO spk_tpenetapan_kursuskemahiran (id_kursus, id_kemahiran, created_by, created_date) VALUES (:idKursus, :kemahiran, :stafID, NOW())";
                $stmtKem = $this->pdoSPK->prepare($sqlKem);
                foreach ($kemahiranArr as $idKemahiran) {
                    $stmtKem->execute([':idKursus' => $idKursus, ':kemahiran' => (int)$idKemahiran, ':stafID' => $stafID]);
                }
            }

            $this->pdoSPK->prepare("DELETE FROM spk_tpenetapan_kursuspenilaian WHERE id_kursus = :idKursus")->execute([':idKursus' => $idKursus]);
            
            if (!empty($penilaianArr)) {
                $sqlPen = "INSERT INTO spk_tpenetapan_kursuspenilaian (id_kursus, id_penilaian, percentage, f2f, nf2f, slt, created_by, created_date) 
                           VALUES (:idKursus, :idPenilaian, :pct, :f2f, :nf2f, :slt, :stafID, NOW())";
                $stmtPen = $this->pdoSPK->prepare($sqlPen);
                foreach ($penilaianArr as $pen) {
                    $stmtPen->execute([
                        ':idKursus'    => $idKursus,
                        ':idPenilaian' => $pen['id_penilaian'],
                        ':pct'         => $pen['percentage'],
                        ':f2f'         => $pen['f2f'],
                        ':nf2f'        => $pen['nf2f'],
                        ':slt'         => $pen['slt'],
                        ':stafID'      => $stafID
                    ]);
                }
            }

            $this->pdoSPK->prepare("DELETE FROM spk_treference WHERE id_kursus = :idKursus")->execute([':idKursus' => $idKursus]);
            
            if (!empty($rujukanArr)) {
                $sqlRef = "INSERT INTO spk_treference (reference_desc, id_kursus, created_by, created_date) VALUES (:refDesc, :idKursus, :stafID, NOW())";
                $stmtRef = $this->pdoSPK->prepare($sqlRef);
                foreach ($rujukanArr as $ref) {
                    $stmtRef->execute([':refDesc' => $ref, ':idKursus' => $idKursus, ':stafID' => $stafID]);
                }
            }

            $this->pdoSPK->commit();
            return true;

        } catch (Exception $e) {
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }
            throw $e;
        }
    }
}