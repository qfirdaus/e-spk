<?php
declare(strict_types=1);

class Kemahiran
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
    }
    public function getSkillList(): array
    {       
        $sql = "SELECT * FROM spk_tkemahiran WHERE status_aktif=1 ORDER BY kemahiran";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // simpan Skill baru
    public function addSkillBaharu(array $data): bool 
    {
        $kemahiran = $data['txtkemahiran'] ?? null;
        $created_by = $data['created_by'] ?? null;

        try {
            $this->pdoSPK->beginTransaction();
    
            $sql = "INSERT INTO spk_tkemahiran(kemahiran, created_by, created_date)
                    VALUES (:kemahiran, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            
            $stmt->execute([
                ':kemahiran' => $kemahiran,
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

    public function updateDataSkill(array $data): bool 
    {
        $idKemahiran = $data['txtidkemahiran_edit'] ?? null;
        $kemahiran   = $data['txtkemahiran_edit'] ?? null;
        $updated_by  = $data['updated_by'] ?? null;    

        if (!$idKemahiran) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tkemahiran 
                    SET kemahiran = :kemahiran, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE id_kemahiran = :id_kemahiran";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':kemahiran' => $kemahiran,
                ':updated_by' => $updated_by,
                ':id_kemahiran'     => $idKemahiran
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

    public function deleteDataSkill(array $data): bool 
    {
        $idKemahiran = $data['idKemahiran'] ?? null;
        $deleted_by = $data['deleted_by'] ?? null;
        $status_Skill = 0; // 0 = deleted

        if (!$idKemahiran) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();
  
            $sql = "UPDATE spk_tkemahiran 
                    SET status_aktif = :statusSkill, 
                        deleted_by = :deleted_by, 
                        deleted_date = NOW() 
                    WHERE id_kemahiran = :id_kemahiran";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':statusSkill' => $status_Skill,
                ':deleted_by' => $deleted_by,
                ':id_kemahiran' => $idKemahiran
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