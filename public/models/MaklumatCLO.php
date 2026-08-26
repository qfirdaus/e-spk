<?php
declare(strict_types=1);

class MaklumatCLO
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

    public function getSesiList(string $kodTerm): array
    {
        $sql = "SELECT DISTINCT sesi2, f005term, semester FROM v005_spk WHERE $kodTerm ORDER BY f005term DESC";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSelectedTermDetail(string $term): array
    {
        $sql = "SELECT * FROM v005_spk WHERE f005term = :term";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':term' => $term]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }    

    public function getKursusList(string $sesi, string $stafID): array
    {
        if (empty($sesi) || empty($stafID)) {
            return [];
        }

        $sqlMySQL = "SELECT DISTINCT id_kursus, kod_kursus 
                     FROM spk_tkursus 
                     WHERE status_aktif = 1 AND penyelaras_kursus = :stafID";
        $stmtSPK = $this->pdoSPK->prepare($sqlMySQL);
        $stmtSPK->execute([':stafID' => $stafID]);
        $senaraiSPK = $stmtSPK->fetchAll(PDO::FETCH_ASSOC);

        if (empty($senaraiSPK)) {
            return [];
        }
        
        $kodArray = [];
        $mapKursus = [];
        
        foreach ($senaraiSPK as $k) {
            // Tukar jadi HURUF BESAR & buang space untuk elak masalah cantuman
            $kod = strtoupper(trim($k['kod_kursus'])); 
            $kod_escaped = str_replace("'", "''", $kod);
            $kodArray[] = "'" . $kod_escaped . "'"; 
            
            // Simpan dalam map menggunakan kunci huruf besar
            $mapKursus[$kod] = $k; 
        }        
        
        $inClause = implode(',', $kodArray);

        $stafParts = explode('-', $stafID);
        $stafnoClean = ltrim($stafParts[0], '0'); // eg:556

        $sqlSybase = "SELECT kodk, subjekbm, subjekbi 
                      FROM v270offer_spk 
                      WHERE term = :sesi 
                      AND stafno = :stafno
                      AND kodk IN ($inClause)
                      GROUP BY kodk, subjekbm, subjekbi";
        $stmtStudent = $this->pdoStudent->prepare($sqlSybase);
        $stmtStudent->execute([
            ':sesi'   => $sesi,
            ':stafno' => $stafnoClean
        ]);

        $senaraiSybase = $stmtStudent->fetchAll(PDO::FETCH_ASSOC);

        $finalList = [];
        foreach ($senaraiSybase as $sybaseRow) {
            $kodk = strtoupper(trim($sybaseRow['kodk']));
            
            if (isset($mapKursus[$kodk])) {
                $finalList[] = [
                    'id_kursus'  => $mapKursus[$kodk]['id_kursus'],
                    'kod_kursus' => $mapKursus[$kodk]['kod_kursus'], // Kekalkan format asal MySQL
                    'kodk'       => $sybaseRow['kodk'],
                    'subjekbm'   => $sybaseRow['subjekbm'],
                    'subjekbi'   => $sybaseRow['subjekbi']
                ];
            }
        }

        return $finalList;
    }

    // List Kaedah Pengajaran
    public function getKaedahList(): array
    {
        $sql = "SELECT * FROM spk_tkaedah_pengajaran WHERE status_aktif = 1";
        return $this->pdoSPK->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // List Penilaian
    public function getPenilaianList(): array
    {
        $sql = "SELECT * FROM spk_tpenilaian WHERE status_aktif = 1";
        return $this->pdoSPK->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // List CLO 
    public function getCLOList(string $sesi, int $kursusID): array
    {
        $sql = "
            SELECT 
                c.id_clo, 
                c.kod_clo, 
                c.keterangan_bm,
                (SELECT GROUP_CONCAT(p.kod_plo SEPARATOR ', ') 
                 FROM spk_tpenetapan_plo_clo pc 
                 JOIN spk_tplo p ON pc.id_plo = p.id_plo 
                 WHERE pc.id_clo = c.id_clo) AS senarai_plo_string,
                 
                (SELECT GROUP_CONCAT(k.kaedah_pengajaran SEPARATOR ', ') 
                 FROM spk_tpenetapan_clo_kpengajaran ck 
                 JOIN spk_tkaedah_pengajaran k ON ck.id_kaedah_pengajaran = k.id_kaedah_pengajaran 
                 WHERE ck.id_clo = c.id_clo) AS senarai_kaedah_string,
                 
                (SELECT GROUP_CONCAT(n.penilaian SEPARATOR ', ') 
                 FROM spk_tpenetapan_clo_penilaian cn 
                 JOIN spk_tpenilaian n ON cn.id_penilaian = n.id_penilaian 
                 WHERE cn.id_clo = c.id_clo) AS senarai_penilaian_string,

                (SELECT GROUP_CONCAT(id_plo) FROM spk_tpenetapan_plo_clo WHERE id_clo = c.id_clo) AS plo_ids,
                (SELECT GROUP_CONCAT(id_kaedah_pengajaran) FROM spk_tpenetapan_clo_kpengajaran WHERE id_clo = c.id_clo) AS kaedah_ids,
                (SELECT GROUP_CONCAT(id_penilaian) FROM spk_tpenetapan_clo_penilaian WHERE id_clo = c.id_clo) AS penilaian_ids

            FROM spk_tclo c
            WHERE c.status_aktif = 1 
            AND c.kod_sesi = :sesi 
            AND c.id_kursus = :kursusID
            ORDER BY c.kod_clo ASC
        ";
        
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':kursusID' => $kursusID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    // Senarai Data untuk Checkbox (PLO, Kaedah, Penilaian)
    public function getPloList(string $sesi, int $idKursus): array
    {
        $sql = "SELECT DISTINCT id_plo, kod_plo, keterangan_bm 
                FROM spk_tkursus k 
                JOIN spk_tplo p ON k.program_universiti = p.program_universiti 
                WHERE kod_sesi = :sesi AND p.status_aktif = 1 AND id_kursus = :idKursus 
                AND COALESCE(k.kod_program,'-') = COALESCE(p.kod_program,'-')";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':idKursus' => $idKursus]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKodJabatanStaf(string $stafID): string
    {
        $sql = "SELECT kdjbtnsemasa FROM v630staf_service_skim_all WHERE nopekerja = :stafID";
        $stmt = $this->pdoStaff->prepare($sql);
        $stmt->execute([':stafID' => $stafID]);
        $ptj = $stmt->fetchColumn();
        
        return $ptj ? (string)$ptj : '';
    }

    // Tambah CLO
    public function addCLO(array $formData, array $plo, array $penilaian, array $kaedah): bool
    {
        try {
            $this->pdoSPK->beginTransaction();

            $sql = "INSERT INTO spk_tclo (kod_clo, keterangan_bm, kod_sesi, kod_jabatan, kod_program, id_kursus, created_by, created_date) 
                    VALUES (:kodclo, :keterangan, :sesi, :ptj, :programid, :kursusid, :created_by, NOW())";
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':kodclo'     => $formData['txtkodclo'],
                ':keterangan' => $formData['txtketeranganclo'],
                ':sesi'       => $formData['txtsesiid'],
                ':ptj'        => $formData['ptj'],
                ':programid'  => $formData['txtprogramid'] ?? '', 
                ':kursusid'   => $formData['txtkursusid'],
                ':created_by' => $formData['created_by']
            ]);

            $cloID = $this->pdoSPK->lastInsertId(); 

            if (!empty($plo)) {
                $sqlPlo = "INSERT INTO spk_tpenetapan_plo_clo (id_plo, id_clo, created_by, created_date) VALUES (:id_plo, :id_clo, :created_by, NOW())";
                $stmtPlo = $this->pdoSPK->prepare($sqlPlo);
                foreach ($plo as $id_plo) {
                    $stmtPlo->execute([':id_plo' => $id_plo, ':id_clo' => $cloID, ':created_by' => $formData['created_by']]);
                }
            }

            if (!empty($penilaian)) {
                $sqlNilai = "INSERT INTO spk_tpenetapan_clo_penilaian (id_penilaian, id_clo, created_by, created_date) VALUES (:id_nilai, :id_clo, :created_by, NOW())";
                $stmtNilai = $this->pdoSPK->prepare($sqlNilai);
                foreach ($penilaian as $id_nilai) {
                    $stmtNilai->execute([':id_nilai' => $id_nilai, ':id_clo' => $cloID, ':created_by' => $formData['created_by']]);
                }
            }

            if (!empty($kaedah)) {
                $sqlKaedah = "INSERT INTO spk_tpenetapan_clo_kpengajaran (id_kaedah_pengajaran, id_clo, created_by, created_date) VALUES (:id_kaedah, :id_clo, :created_by, NOW())";
                $stmtKaedah = $this->pdoSPK->prepare($sqlKaedah);
                foreach ($kaedah as $id_kaedah) {
                    $stmtKaedah->execute([':id_kaedah' => $id_kaedah, ':id_clo' => $cloID, ':created_by' => $formData['created_by']]);
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

    // Update CLO     
    public function updateCLO(int $cloID, array $data, array $plo, array $penilaian, array $kaedah, string $stafID): bool
    {
        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tclo SET keterangan_bm = :keterangan, updated_by = :updated_by, updated_date = NOW() WHERE id_clo = :id_clo";
            $this->pdoSPK->prepare($sql)->execute([':keterangan' => $data['txtketeranganclo'], ':updated_by' => $stafID, ':id_clo' => $cloID]);

            $this->pdoSPK->prepare("DELETE FROM spk_tpenetapan_plo_clo WHERE id_clo = :id_clo")->execute([':id_clo' => $cloID]);
            if (!empty($plo)) {
                $sqlPlo = "INSERT INTO spk_tpenetapan_plo_clo (id_plo, id_clo, created_by, created_date) VALUES (:id_plo, :id_clo, :created_by, NOW())";
                $stmtPlo = $this->pdoSPK->prepare($sqlPlo);
                foreach ($plo as $id_plo) {
                    $stmtPlo->execute([':id_plo' => $id_plo, ':id_clo' => $cloID, ':created_by' => $stafID]);
                }
            }

            $this->pdoSPK->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdoSPK->rollBack();
            throw $e;
        }
    }

    // Hapus CLO 
    public function deleteCLO(int $cloID, string $stafID): bool
    {
        $sql = "UPDATE spk_tclo SET status_aktif = 0, deleted_by = :deleted_by, deleted_date = NOW() WHERE id_clo = :id_clo";
        return $this->pdoSPK->prepare($sql)->execute([':deleted_by' => $stafID, ':id_clo' => $cloID]);
    }
}