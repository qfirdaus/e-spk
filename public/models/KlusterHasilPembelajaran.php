<?php
declare(strict_types=1);

class KlusterHasilPembelajaran
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
    }
    public function getLocList(): array
    {        
        $sql = "SELECT * FROM spk_tloc WHERE status_aktif=1 ORDER BY loc";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // simpan LOC baru
    public function addLocBaharu(array $data): bool 
    {
        $loc_text = $data['txtloc'] ?? null;
        $created_by = $data['created_by'] ?? null;

        try {
            $this->pdoSPK->beginTransaction();
            
            $sql = "INSERT INTO spk_tloc(loc, created_by, created_date)
                    VALUES (:loc, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            
            $stmt->execute([
                ':loc'        => $loc_text,
                ':created_by' => $created_by
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

    public function updateDataLOC(array $data): bool 
    {
        $idLoc      = $data['txtidloc_edit'] ?? null;
        $loc        = $data['txtloc_edit'] ?? null;
        $updated_by = $data['updated_by'] ?? null;    

        if (!$idLoc) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tloc 
                    SET loc = :loc, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE id_loc = :id_loc";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':loc'        => $loc,
                ':updated_by' => $updated_by,
                ':id_loc'     => $idLoc
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

    public function deleteDataLOC(array $data): bool 
    {
        $idLoc = $data['idloc'] ?? null;
        $deleted_by = $data['deleted_by'] ?? null;
        $status_LOC = 0; // 0 = deleted

        if (!$idLoc) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();
            
            $sql = "UPDATE spk_tloc 
                    SET status_aktif = :status_LOC, 
                        deleted_by = :deleted_by, 
                        deleted_date = NOW() 
                    WHERE id_loc = :id_loc";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':status_LOC'        => $status_LOC,
                ':deleted_by' => $deleted_by,
                ':id_loc'     => $idLoc
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