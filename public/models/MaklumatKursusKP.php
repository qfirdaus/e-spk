<?php
declare(strict_types=1);

class MaklumatKursusKP
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;
    private PDO $pdoStaff;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent, PDO $pdoStaff)
    {
        $this->pdoSPK     = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
        $this->pdoStaff   = $pdoStaff;
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

    public function getProgramList(string $tahapPengajian, string $ptj): array
    {
        if (empty($tahapPengajian) || empty($ptj)) return [];

        $sql = "SELECT * FROM v006_spk 
                WHERE LTRIM(RTRIM(tahap_pengajian)) = :tahap 
                AND kdjbt = :ptj 
                ORDER BY program";

        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([
            ':tahap' => trim($tahapPengajian),
            ':ptj'   => trim($ptj)
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSelectedTermDetail(string $term): array
    {
        $sql = "SELECT * FROM v005_spk WHERE f005term = :term";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':term' => $term]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getSelectedProgramDetail(string $idProgram): array
    {
        $sql = "SELECT * FROM v006_spk WHERE id_program = :id_program";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':id_program' => $idProgram]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function syncOfferKursusToSPK(string $sesi, string $ptj, string $stafID): void
    {
        $sqlOffer = "SELECT DISTINCT a.kodk FROM v270offer_spk a
                    LEFT JOIN t776jabatan b ON a.kdfakulti = b.f776singkat
                    WHERE a.term = :sesi AND b.f776kdjbt = :ptj";
        $stmtOffer = $this->pdoStudent->prepare($sqlOffer);
        $stmtOffer->execute([':sesi' => $sesi, ':ptj' => $ptj]);
        $offerList = $stmtOffer->fetchAll(PDO::FETCH_ASSOC);

        $sqlCheck = "SELECT COUNT(*) FROM spk_tkursus WHERE kod_kursus = :kodk AND term_pengajian = :sesi";
        $stmtCheck = $this->pdoSPK->prepare($sqlCheck);

        $sqlInsert = "INSERT INTO spk_tkursus (kod_kursus, term_pengajian, kod_jabatan, created_by, created_date) 
                      VALUES (:kodk, :sesi, :ptj, :created_by, NOW())";
        $stmtInsert = $this->pdoSPK->prepare($sqlInsert);

        foreach ($offerList as $row) {
            $stmtCheck->execute([':kodk' => $row['kodk'], ':sesi' => $sesi]);
            if ((int)$stmtCheck->fetchColumn() === 0) {
                $stmtInsert->execute([
                    ':kodk'       => $row['kodk'],
                    ':sesi'       => $sesi,
                    ':ptj'        => $ptj,
                    ':created_by' => $stafID
                ]);
            }
        }
    }

    public function getKursusList(string $sesi, string $ptj, string $idProgram): array
    {
        if (empty($sesi) || empty($ptj) || empty($idProgram)) {
            return [];
        }

        $sqlSPK = "SELECT id_kursus, kod_kursus, term_pengajian, kategori_kursus, penyelaras_kursus 
                FROM spk_tkursus 
                WHERE term_pengajian = :sesi 
                AND kod_jabatan = :ptj 
                AND kod_program = :program 
                AND program_universiti = 'Program' 
                ORDER BY kod_kursus";

        $stmtSPK = $this->pdoSPK->prepare($sqlSPK);
        $stmtSPK->execute([
            ':sesi'    => $sesi,
            ':ptj'     => $ptj,
            ':program' => $idProgram
        ]);
        $listKursus = $stmtSPK->fetchAll(PDO::FETCH_ASSOC);

        if (empty($listKursus)) {
            return [];
        }

        $sqlStudent = "SELECT DISTINCT kodk, subjekbm FROM v270offer_spk a
                    LEFT JOIN t776jabatan b ON a.kdfakulti = b.f776singkat
                    WHERE term = :sesi AND b.f776kdjbt = :ptj";
        $stmtStudent = $this->pdoStudent->prepare($sqlStudent);
        $stmtStudent->execute([
            ':sesi' => $sesi,
            ':ptj'  => $ptj
        ]);
        $offerMap = $stmtStudent->fetchAll(PDO::FETCH_KEY_PAIR); // array [ 'KODK' => 'SUBJEKBM' ]

        foreach ($listKursus as &$row) {
            $kodk = $row['kod_kursus'];
            $row['subjekbm'] = $offerMap[$kodk] ?? '-';
        }

        return $listKursus;
    }

    public function getOfferKursusDropdown(string $sesi, string $ptj): array
    {
        $sql = "SELECT DISTINCT a.kodk, a.subjekbm FROM v270offer_spk a
                LEFT JOIN t776jabatan b ON a.kdfakulti = b.f776singkat
                WHERE a.term = :sesi AND b.f776kdjbt = :ptj ORDER BY a.kodk";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':sesi' => $sesi, ':ptj' => $ptj]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPensyarahOptions(string $kodKursus, string $sesi): array
    {
        $sql = "SELECT DISTINCT s.nopekerja, s.gelar_nama 
                FROM v270offer_spk a 
                JOIN ehrmdb.dbo.v630staf_service_skim_aktif s ON a.stafno = CONVERT(VARCHAR(10), s.idpekerja) 
                WHERE a.kodk = :kodk AND a.term = :sesi";

        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':kodk' => $kodKursus, ':sesi' => $sesi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addKursus(array $data): bool
    {
        $sql = "INSERT INTO spk_tkursus 
                (program_universiti, kod_kursus, term_pengajian, kategori_kursus, kod_program, kod_jabatan, created_by, created_date) 
                VALUES ('Program', :kod_kursus, :sesi, :kategori, :program, :ptj, :created_by, NOW())";

        $stmt = $this->pdoSPK->prepare($sql);
        return $stmt->execute([
            ':kod_kursus' => $data['selectkursus'],
            ':sesi'       => $data['txtsesiid'],
            ':kategori'   => $data['selectKategoriKursus'],
            ':program'    => $data['txtprogramid'],
            ':ptj'        => $data['ptj'],
            ':created_by' => $data['created_by']
        ]);
    }

    public function updateKategori(int $idKursus, string $kategori, string $idProgram, string $stafID): bool
    {
        $sql = "UPDATE spk_tkursus 
                SET kod_program = :program, kategori_kursus = :kategori, updated_by = :staf_id, updated_date = NOW() 
                WHERE id_kursus = :id_kursus";
        $stmt = $this->pdoSPK->prepare($sql);
        return $stmt->execute([
            ':program'   => $idProgram,
            ':kategori'  => $kategori,
            ':staf_id'   => $stafID,
            ':id_kursus' => $idKursus
        ]);
    }

    public function resetPenyelaras(int $idKursus, string $stafIDUpdatedBy): bool
    {
        try {
            $this->pdoSPK->beginTransaction();
            
            $sqlGetOld = "SELECT penyelaras_kursus FROM spk_tkursus WHERE id_kursus = :id_kursus LIMIT 1";
            $stmtGetOld = $this->pdoSPK->prepare($sqlGetOld);
            $stmtGetOld->execute([':id_kursus' => $idKursus]);
            $stafIDPenyelarasLama = $stmtGetOld->fetchColumn();

            $sql = "UPDATE spk_tkursus 
                    SET penyelaras_kursus = NULL, updated_by = :updated_by, updated_date = NOW() 
                    WHERE id_kursus = :id_kursus";
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':updated_by' => $stafIDUpdatedBy,
                ':id_kursus'  => $idKursus
            ]);

            if (!empty($stafIDPenyelarasLama)) {
                $checkSql = "SELECT COUNT(id_kursus) FROM spk_tkursus WHERE penyelaras_kursus = :stafID";
                $checkStmt = $this->pdoSPK->prepare($checkSql);
                $checkStmt->execute([':stafID' => $stafIDPenyelarasLama]);
                $count = (int)$checkStmt->fetchColumn();

                // block akses ADM-COORDINATOR 
                if ($count === 0) {
                    
                    $targetGroupID = 23; // groupID untuk Penyelaras Kursus

                    $userCheckSql = "SELECT f_userID, f_groupID FROM tbl_m_user WHERE f_stafID = :stafID LIMIT 1";
                    $userCheckStmt = $this->pdoSPK->prepare($userCheckSql);
                    $userCheckStmt->execute([':stafID' => $stafIDPenyelarasLama]);
                    $userMain = $userCheckStmt->fetch(\PDO::FETCH_ASSOC);

                    if ($userMain) {
                        $currentMainGroupID = (int)$userMain['f_groupID'];

                        // if Penyelaras merupakan main role
                        if ($currentMainGroupID === $targetGroupID) {
                            $sqlUser = "UPDATE tbl_m_user 
                                        SET f_flag = 0,  
                                            f_updateby = :updatedBy, 
                                            f_updatedt = NOW(),
                                            f_remarks = 'Akaun disekat kerana bukan lagi Penyelaras bagi mana-mana Kursus.'
                                        WHERE f_stafID = :stafID";
                            $stmtUser = $this->pdoSPK->prepare($sqlUser);
                            $stmtUser->execute([
                                ':updatedBy' => $stafIDUpdatedBy,
                                ':stafID'    => $stafIDPenyelarasLama
                            ]);
                        } 
                        // if Penyelaras bukan main role    
                        else {
                            $sqlAccess = "UPDATE tbl_ref_access 
                                          SET f_status = 0, 
                                              f_updatedby = :updatedBy, 
                                              f_updateddt = NOW() 
                                          WHERE f_stafID = :stafID AND f_groupID = :groupID";
                            $stmtAccess = $this->pdoSPK->prepare($sqlAccess);
                            $stmtAccess->execute([
                                ':updatedBy' => $stafIDUpdatedBy,
                                ':stafID'    => $stafIDPenyelarasLama,
                                ':groupID'   => $targetGroupID
                            ]);
                        }
                    }
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

    public function updatePenyelaras(int $idKursus, string $stafIDPenyelaras, string $ptj, string $stafIDCreatedBy): bool
    {
        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tkursus 
                    SET penyelaras_kursus = :penyelaras, updated_by = :created_by, updated_date = NOW() 
                    WHERE id_kursus = :id_kursus";
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':penyelaras' => $stafIDPenyelaras,
                ':created_by' => $stafIDCreatedBy,
                ':id_kursus'  => $idKursus
            ]);

            $defaultGroupID  = 23; // group id : Penyelaras Kursus
            $defaultGroupKod = 'ADM-COORDINATOR';
            $remarks         = "Akaun didaftar/dikemaskini sebagai Penyelaras Kursus";

            // check tbl_m_user
            $checkSql = "SELECT f_userID, f_flag, f_groupID FROM tbl_m_user WHERE f_stafID = :staff_id LIMIT 1";
            $checkStmt = $this->pdoSPK->prepare($checkSql);
            $checkStmt->execute([':staff_id' => $stafIDPenyelaras]);
            $userExists = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($userExists) {
                $userID         = (int)$userExists['f_userID'];
                $currentFlag    = (int)$userExists['f_flag'];
                $currentGroupID = (int)$userExists['f_groupID'];

                // disekat (0); aktif semula
                if ($currentFlag === 0) {
                    if ($currentGroupID === $defaultGroupID) {
                        $updateSql = "UPDATE tbl_m_user 
                                      SET f_flag = 1, f_updateby = :updateby, f_updatedt = NOW(), f_remarks = :remarks 
                                      WHERE f_userID = :userID";
                        $this->pdoSPK->prepare($updateSql)->execute([
                            ':updateby' => $stafIDCreatedBy, 
                            ':remarks'  => 'Akaun Penyelaras diaktifkan semula.', 
                            ':userID'   => $userID
                        ]);
                    } else {
                        $updateSql = "UPDATE tbl_m_user 
                                      SET f_flag = 1, f_groupID = :groupID, f_groupKod = :groupKod, f_updateby = :updateby, f_updatedt = NOW(), f_remarks = :remarks 
                                      WHERE f_userID = :userID";
                        $this->pdoSPK->prepare($updateSql)->execute([
                            ':groupID'  => $defaultGroupID, 
                            ':groupKod' => $defaultGroupKod, 
                            ':updateby' => $stafIDCreatedBy, 
                            ':remarks'  => 'Akaun diaktifkan semula dan ditukar kepada peranan Penyelaras.', 
                            ':userID'   => $userID
                        ]);
                    }
                } 
                // akaun aktif, pastikan multiple role disetkan jika ID group berbeza
                else {
                    if ($currentGroupID !== $defaultGroupID) {
                        $accessCheckSql = "SELECT f_accessID, f_status FROM tbl_ref_access WHERE f_stafID = :stafID AND f_groupID = :groupID LIMIT 1";
                        $accessCheckStmt = $this->pdoSPK->prepare($accessCheckSql);
                        $accessCheckStmt->execute([':stafID' => $stafIDPenyelaras, ':groupID' => $defaultGroupID]);
                        $accessExists = $accessCheckStmt->fetch(\PDO::FETCH_ASSOC);

                        if (!$accessExists) {
                            $accessSql = "INSERT INTO tbl_ref_access (f_stafID, f_userID, f_groupID, f_status, f_createdby, f_createddt) 
                                          VALUES (:stafID, :userID, :groupID, 1, :createdby, NOW())";
                            $this->pdoSPK->prepare($accessSql)->execute([
                                ':stafID'    => $stafIDPenyelaras, 
                                ':userID'    => $userID, 
                                ':groupID'   => $defaultGroupID, 
                                ':createdby' => $stafIDCreatedBy
                            ]);
                        } else {
                            if ((int)$accessExists['f_status'] === 0) {
                                $accessUpdateSql = "UPDATE tbl_ref_access SET f_status = 1, f_updatedby = :updatedby, f_updateddt = NOW() 
                                                    WHERE f_stafID = :stafID AND f_groupID = :groupID";
                                $this->pdoSPK->prepare($accessUpdateSql)->execute([
                                    ':updatedby' => $stafIDCreatedBy, 
                                    ':stafID'    => $stafIDPenyelaras, 
                                    ':groupID'   => $defaultGroupID
                                ]);
                            }
                        }
                    }
                }
            } else {
                // akaun tak wujud, ambil data dari Sybase, register
                $sybaseSql = "SELECT nopekerja, idpekerja, gelar_nama, nama, nokp, email, handphone, telefon_pej,
                                     kdjwtsemasa, jawatansemasa, kdjenis, jenis, kdjbtnsemasa, 
                                     jabatansemasa, kumpjwt, kodstatus, status 
                              FROM v630staf_service_skim_all
                              WHERE nopekerja = :nopekerja AND CONVERT(INT, kodstatus) = 1";
                $sybaseStmt = $this->pdoStaff->prepare($sybaseSql);
                $sybaseStmt->execute([':nopekerja' => $stafIDPenyelaras]);
                $sybaseUser = $sybaseStmt->fetch(\PDO::FETCH_ASSOC);

                if ($sybaseUser) {
                    $nokp = $sybaseUser['nokp'] ?? '';
                    $hashedPassword = !empty($nokp) ? password_hash($nokp, PASSWORD_DEFAULT) : '';
                    
                    $insertSql = "INSERT INTO tbl_m_user (
                                    f_loginID, f_stafID, f_categoryUser, f_nopekerja, f_nama, f_nickname, 
                                    f_nokp, f_password, f_email, f_handphone, f_telefon_pej, f_jawatanKod, f_jawatan, 
                                    f_jenisID, f_jenis, f_jabatanKod, f_namajabatan, f_kumpjawatan, 
                                    f_verified_at, f_must_change_password, f_statusID, f_status, 
                                    f_groupID, f_groupKod, f_flag, f_insertdt, f_updatedt, f_updateby, f_remarks
                                ) VALUES (
                                    :login, :stafID, 'STAF', :nopekerja, :nama, :nickname, 
                                    :nokp, :pass, :email, :phone, :tel_pej, :jawatanKod, :jawatan, 
                                    :jenisID, :jenis, :jabatanKod, :namajabatan, :kumpjawatan, 
                                    NOW(), 0, :statusID, :status, 
                                    :groupID, :groupKod, 1, NOW(), NOW(), :updateby, :remarks
                                )";
                    $insertStmt = $this->pdoSPK->prepare($insertSql);
                    $insertStmt->execute([
                        ':login'        => $stafIDPenyelaras,
                        ':stafID'       => $stafIDPenyelaras,
                        ':nopekerja'    => $sybaseUser['idpekerja'] ?? null,
                        ':nama'         => $sybaseUser['gelar_nama'] ?? null,
                        ':nickname'     => $sybaseUser['nama'] ?? null,
                        ':nokp'         => $sybaseUser['nokp'] ?? null,
                        ':pass'         => $hashedPassword,
                        ':email'        => $sybaseUser['email'] ?? null,
                        ':phone'        => $sybaseUser['handphone'] ?? null,
                        ':tel_pej'      => $sybaseUser['telefon_pej'] ?? null,
                        ':jawatanKod'   => $sybaseUser['kdjwtsemasa'] ?? null,
                        ':jawatan'      => $sybaseUser['jawatansemasa'] ?? null,
                        ':jenisID'      => !empty($sybaseUser['kdjenis']) ? (int)$sybaseUser['kdjenis'] : null,
                        ':jenis'        => $sybaseUser['jenis'] ?? null,
                        ':jabatanKod'   => $sybaseUser['kdjbtnsemasa'] ?? null,
                        ':namajabatan'  => $sybaseUser['jabatansemasa'] ?? null,
                        ':kumpjawatan'  => $sybaseUser['kumpjwt'] ?? null,
                        ':statusID'     => !empty($sybaseUser['kodstatus']) ? (int)$sybaseUser['kodstatus'] : null,
                        ':status'       => $sybaseUser['status'] ?? null,
                        ':groupID'      => $defaultGroupID,
                        ':groupKod'     => $defaultGroupKod,
                        ':updateby'     => $stafIDCreatedBy,
                        ':remarks'      => $remarks
                    ]);
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
}