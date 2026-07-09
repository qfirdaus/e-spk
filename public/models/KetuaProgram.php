<?php
declare(strict_types=1);

class KetuaProgram
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

    public function stafList($term) 
    {
        try {
            // Guna query Sybase kau yang asal tadi
            $sql = "SELECT TOP 5 *, gelar_nama + ' ' + nama AS nama_staf FROM ehrmdb.dbo.v630staf_service_skim_aktif staf
                        WHERE LOWER(gelar_nama + ' ' + nama) LIKE LOWER(:term1)
                        OR LOWER(nopekerja) LIKE LOWER(:term2)";
                    
            $stmt = $this->pdoStudent->prepare($sql);
            $stmt->execute([
                ':term1' => "%{$term}%",
                ':term2' => "{$term}%"
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getKetuaProgramList(): array
    {            
        $sql = "SELECT * FROM tbl_m_user 
                WHERE f_flag = 1 /* 1 = dibenarkan, 0 - disekat */
                AND f_groupID = 28 /* 28 = Ketua Program */
                ORDER BY f_stafID ASC";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // simpan Ketua Program baru
    public function addHeadProgrammeBaharu($formData): bool 
    {
        try {         
            $nopekerja = trim((string)($formData['txtnostaf'] ?? ''));

            if (empty($nopekerja)) {
                throw new \Exception('No. Pekerja tidak sah atau kosong.');
            }

            $this->pdoSPK->beginTransaction();

            $defaultGroupID = 28; 
            $defaultGroupKod = 'ADM-KP';
            $remarks = "Added/Updated via Tambah Ketua Program form (SPK)";

            $checkSql = "SELECT f_userID, f_flag, f_groupID FROM tbl_m_user WHERE f_stafID = :staff_id OR f_loginID = :login_id LIMIT 1";
            $checkStmt = $this->pdoSPK->prepare($checkSql);
            $checkStmt->execute([':staff_id' => $nopekerja, ':login_id' => $nopekerja]);
            $userExists = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($userExists) {
                $userID = (int)$userExists['f_userID'];
                $currentFlag = (int)$userExists['f_flag'];
                $currentGroupID = (int)$userExists['f_groupID'];

                if ($currentFlag === 0) {
                    if ($currentGroupID === $defaultGroupID) {

                        $updateSql = "UPDATE tbl_m_user 
                                      SET f_flag = 1, f_updateby = :updateby, f_updatedt = NOW(), f_remarks = :remarks 
                                      WHERE f_userID = :userID";
                        $updateStmt = $this->pdoSPK->prepare($updateSql);
                        $updateStmt->execute([
                            ':updateby' => $formData['created_by'],
                            ':remarks'  => 'Akaun Ketua Program diaktifkan semula.',
                            ':userID'   => $userID
                        ]);

                    } else {

                        $updateSql = "UPDATE tbl_m_user 
                                      SET f_flag = 1, f_groupID = :groupID, f_groupKod = :groupKod, f_updateby = :updateby, f_updatedt = NOW(), f_remarks = :remarks 
                                      WHERE f_userID = :userID";
                        $updateStmt = $this->pdoSPK->prepare($updateSql);
                        $updateStmt->execute([
                            ':groupID'  => $defaultGroupID,
                            ':groupKod' => $defaultGroupKod,
                            ':updateby' => $formData['created_by'],
                            ':remarks'  => 'Akaun diaktifkan semula dan ditukar kepada peranan Ketua Program.',
                            ':userID'   => $userID
                        ]);
                    }

                } else {

                    if ($currentGroupID === $defaultGroupID) {
                        // User sudah pun aktif sebagai Ketua Program di akaun utama.
                    } else {
                        // multiple role
                        $accessCheckSql = "SELECT f_accessID, f_status FROM tbl_ref_access WHERE f_stafID = :stafID AND f_groupID = :groupID LIMIT 1";
                        $accessCheckStmt = $this->pdoSPK->prepare($accessCheckSql);
                        $accessCheckStmt->execute([':stafID' => $nopekerja, ':groupID' => $defaultGroupID]);
                        $accessExists = $accessCheckStmt->fetch(\PDO::FETCH_ASSOC);

                        if (!$accessExists) {
                            $accessSql = "INSERT INTO tbl_ref_access (
                                            f_stafID, f_userID, f_groupID, f_status, f_createdby, f_createddt
                                          ) VALUES (
                                            :stafID, :userID, :groupID, 1, :createdby, NOW()
                                          )";
                            $accessStmt = $this->pdoSPK->prepare($accessSql);
                            $accessStmt->execute([
                                ':stafID'    => $nopekerja,
                                ':userID'    => $userID,
                                ':groupID'   => $defaultGroupID,
                                ':createdby' => $formData['created_by']
                            ]);
                        } else {
                            if ((int)$accessExists['f_status'] === 0) {
                                $accessUpdateSql = "UPDATE tbl_ref_access 
                                                    SET f_status = 1, f_updatedby = :updatedby, f_updateddt = NOW() 
                                                    WHERE f_stafID = :stafID AND f_groupID = :groupID";
                                $accessUpdateStmt = $this->pdoSPK->prepare($accessUpdateSql);
                                $accessUpdateStmt->execute([
                                    ':updatedby' => $formData['created_by'],
                                    ':stafID'    => $nopekerja,
                                    ':groupID'   => $defaultGroupID
                                ]);
                            }
                        }
                    }
                }
            } else {
                // SITUASI 3: Akaun langsung tak wujud di tbl_m_user -> Jalankan pendaftaran penuh dari Sybase
                $sybaseSql = "SELECT nopekerja, idpekerja, gelar_nama, nama, nokp, email, handphone, telefon_pej,
                                     kdjwtsemasa, jawatansemasa, kdjenis, jenis, kdjbtnsemasa, 
                                     jabatansemasa, kumpjwt, kodstatus, status 
                              FROM v630staf_service_skim_all
                              WHERE nopekerja = :nopekerja AND CONVERT(INT, kodstatus) = 1";

                $sybaseStmt = $this->pdoStaff->prepare($sybaseSql);
                $sybaseStmt->execute([':nopekerja' => $nopekerja]);
                $sybaseUser = $sybaseStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$sybaseUser) {
                    throw new \Exception('Staf tidak dijumpai dalam Sybase atau status tidak aktif. Gagal mendaftarkan akaun.');
                }

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
                    ':login'        => $nopekerja,
                    ':stafID'       => $nopekerja,
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
                    ':updateby'     => $formData['created_by'],
                    ':remarks'      => $remarks
                ]);
            }

            // Selesai semua proses tanpa ralat, sahkan transaksi
            $this->pdoSPK->commit();
            return true;

        } catch (\Exception $e) {
            if ($this->pdoSPK->inTransaction()) {
                $this->pdoSPK->rollBack();
            }            
            throw $e;
        }        
    }

    public function deleteDataHeadProgramme(array $data): bool 
    {
        $stafID = $data['stafID'] ?? null;
        $updated_by = $data['updated_by'] ?? null;
        $flag = 0; // 0 = disekat, 1 = dibenarkan

        if (!$stafID) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();
  
            $sql = "UPDATE tbl_m_user 
                    SET f_flag = :flag,  
                        f_updateby = :updatedBy, 
                        f_updatedt = NOW() 
                    WHERE f_stafID = :stafID";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':flag' => $flag,
                ':updatedBy' => $updated_by,
                ':stafID' => $stafID
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
}