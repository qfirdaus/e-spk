<?php
declare(strict_types=1);

class MaklumatPLO
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
    }

    public function getSesiList(string $tahapPengajian): array
    {
        if (empty($tahapPengajian)) return [];
        
        $sql = "SELECT * FROM v005_spk WHERE $tahapPengajian ORDER BY f005term DESC";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProgramList(string $tahapPengajian, string $ptj): array
    {
        $sql = "SELECT * FROM v006_spk WHERE tahap_pengajian = :tahap AND fakulti_singkatan = :ptj ORDER BY program";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([
            ':tahap' => $tahapPengajian,
            ':ptj' => $ptj
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSelectedTerm(string $sesiPlo): array
    {
        $sql = "SELECT * FROM v005_spk WHERE f005term = :sesi";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':sesi' => $sesiPlo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getSelectedProgram(string $programPlo): array
    {
        $sql = "SELECT * FROM v006_spk WHERE id_program = :id_program";
        $stmt = $this->pdoStudent->prepare($sql);
        $stmt->execute([':id_program' => $programPlo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPeoList(string $sesiPlo, string $programPlo): array
    {
        $sesiLike = substr($sesiPlo, 0, -1) . '%';
        
        $sql = "SELECT * FROM spk_tpeo WHERE status_aktif = 1 AND kod_sesi LIKE :sesi AND kod_program = :program ORDER BY kod_peo";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([
            ':sesi' => $sesiLike,
            ':program' => $programPlo
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPloList(string $sesiPlo, string $programUniversiti): array
    {
        $sql = "SELECT * FROM spk_tplo WHERE status_aktif = 1 AND kod_sesi = :sesi AND program_universiti = :program_uni";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute([
            ':sesi' => $sesiPlo,
            ':program_uni' => $programUniversiti
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMqfList(): array
    {
        $sql = "SELECT * FROM spk_tmqf WHERE status_aktif = 1";
        $stmt = $this->pdoSPK->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPloBaharu(array $data): bool 
    {
        $program_universiti = 'Universiti';
        
        $sesiid = $data['txtsesiid'] ?? null;
        $kodplo = $data['selectkodplo'] ?? null;
        $kodmqf = $data['selectkodmqf'] ?? null;
        $keteranganbm = $data['txtketeranganplo'] ?? null;
        $created_by = $data['created_by'] ?? null;
        $chkpeo = $data['chkpeo'] ?? [];

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "INSERT INTO spk_tplo (program_universiti, kod_plo, keterangan_bm, kod_sesi, kod_mqf, created_by, created_date) 
                    VALUES (:program_uni, :kod_plo, :keterangan_bm, :kod_sesi, :kod_mqf, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $result = $stmt->execute([
                ':program_uni'   => $program_universiti,
                ':kod_plo'       => $kodplo,
                ':keterangan_bm' => $keteranganbm,
                ':kod_sesi'      => $sesiid,
                ':kod_mqf'       => $kodmqf,
                ':created_by'    => $created_by
            ]);

            if (!$result) {
                $this->pdoSPK->rollBack();
                return false;
            }

            $plo_id = $this->pdoSPK->lastInsertId();

            if (!empty($chkpeo) && $plo_id) {
                $sql_peo = "INSERT INTO spk_tpenetapan_peo_plo (id_peo, id_plo, created_by, created_date) 
                            VALUES (:id_peo, :id_plo, :created_by, NOW())";
                $stmt_peo = $this->pdoSPK->prepare($sql_peo);

                foreach ($chkpeo as $peo) {
                    $stmt_peo->execute([
                        ':id_peo'     => $peo,
                        ':id_plo'     => $plo_id,
                        ':created_by' => $created_by
                    ]);
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

    public function updateDataPlo(array $data): bool 
    {
        $idplo        = $data['txtidplo'] ?? null;
        $keteranganbm = $data['txtketeranganplo'] ?? null;
        $kodmqf       = $data['selectkodmqf_edit'] ?? null;
        $updated_by   = $data['updated_by'] ?? null;
        $chkpeo       = $data['chkpeo'] ?? [];        

        if (!$idplo) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tplo 
                    SET keterangan_bm = :keterangan_bm, 
                        kod_mqf = :kod_mqf, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE id_plo = :id_plo";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $result = $stmt->execute([
                ':keterangan_bm' => $keteranganbm,
                ':kod_mqf'       => $kodmqf,
                ':updated_by'    => $updated_by,
                ':id_plo'        => $idplo
            ]);

            if (!$result) {
                $this->pdoSPK->rollBack();
                return false;
            }

            $sql_delete = "DELETE FROM spk_tpenetapan_peo_plo WHERE id_plo = :id_plo";
            $stmt_delete = $this->pdoSPK->prepare($sql_delete);
            $stmt_delete->execute([':id_plo' => $idplo]);

            if (!empty($chkpeo)) {
                $sql_peo = "INSERT INTO spk_tpenetapan_peo_plo (id_peo, id_plo, created_by, created_date) 
                            VALUES (:id_peo, :id_plo, :created_by, NOW())";
                $stmt_peo = $this->pdoSPK->prepare($sql_peo);

                foreach ($chkpeo as $peo) {
                    $stmt_peo->execute([
                        ':id_peo'     => $peo,
                        ':id_plo'     => $idplo,
                        ':created_by' => $updated_by 
                    ]);
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

    public function deleteDataPlo(array $data): bool 
    {
        $idplo = $data['id_plo'] ?? null;

        if (!$idplo) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql_delete_peo = "DELETE FROM spk_tpenetapan_peo_plo WHERE id_plo = :id_plo";
            $stmt_peo = $this->pdoSPK->prepare($sql_delete_peo);
            $stmt_peo->execute([':id_plo' => $idplo]);

            $sql_delete_plo = "DELETE FROM spk_tplo WHERE id_plo = :id_plo";
            $stmt_plo = $this->pdoSPK->prepare($sql_delete_plo);
            $result = $stmt_plo->execute([':id_plo' => $idplo]);

            if (!$result) {
                $this->pdoSPK->rollBack();
                return false;
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