<?php
declare(strict_types=1);

class KaedahPengajaran
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
    }
    public function getTeachingMethodList(): array
    {       
        $sql = "SELECT * FROM spk_tkaedah_pengajaran WHERE status_aktif=1 ORDER BY kaedah_pengajaran";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // simpan Teaching Method baru
    public function addTeachMethodBaharu(array $data): bool 
    {
        $kaedah_pengajaran = $data['txtkaedah_pengajaran'] ?? null;
        $created_by = $data['created_by'] ?? null;

        try {
            $this->pdoSPK->beginTransaction();
            
            $sql = "INSERT INTO spk_tkaedah_pengajaran(kaedah_pengajaran, created_by, created_date)
                    VALUES (:kaedahPengajaran, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            
            $stmt->execute([
                ':kaedahPengajaran' => $kaedah_pengajaran,
                ':created_by'       => $created_by
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

    public function updateDataTeachMethod(array $data): bool 
    {
        $id_kaedah_pengajaran = $data['txtidkaedah_pengajaran_edit'] ?? null;
        $kaedah_pengajaran   = $data['txtkaedah_pengajaran_edit'] ?? null;
        $updated_by         = $data['updated_by'] ?? null;    

        if (!$id_kaedah_pengajaran) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tkaedah_pengajaran 
                    SET kaedah_pengajaran = :kaedahPengajaran, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE id_kaedah_pengajaran = :idkaedahPengajaran";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':kaedahPengajaran' => $kaedah_pengajaran,
                ':updated_by' => $updated_by,
                ':idkaedahPengajaran'     => $id_kaedah_pengajaran
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

    public function deleteDataTeachMethod(array $data): bool 
    {
        $idTeachMethod = $data['idTeachMethod'] ?? null;
        $deleted_by = $data['deleted_by'] ?? null;
        $status_TeachMethod = 0; // 0 = deleted

        if (!$idTeachMethod) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();
            
            $sql = "UPDATE spk_tkaedah_pengajaran 
                    SET status_aktif = :statusTeachMethod, 
                        deleted_by = :deleted_by, 
                        deleted_date = NOW() 
                    WHERE id_kaedah_pengajaran = :idKaedahPengajaran";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':statusTeachMethod' => $status_TeachMethod,
                ':deleted_by' => $deleted_by,
                ':idKaedahPengajaran' => $idTeachMethod
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