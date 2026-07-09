<?php
declare(strict_types=1);

class KodMQF
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
    }
    public function getMQFList(): array
    {       
        $sql = "SELECT * FROM spk_tmqf WHERE status_aktif=1 ORDER BY kod_mqf";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // simpan MQF baru
    public function addMQFBaharu(array $data): bool 
    {
        $kodMQF = $data['txtmqf'] ?? null;
        $created_by = $data['created_by'] ?? null;

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "INSERT INTO spk_tmqf(kod_mqf, created_by, created_date)
                    VALUES (:kod_mqf, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            
            $stmt->execute([
                ':kod_mqf' => $kodMQF,
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

    public function updateDataMQF(array $data): bool 
    {
        $id_mqf = $data['txtidmqf_edit'] ?? null;
        $kod_mqf   = $data['txtmqf_edit'] ?? null;
        $updated_by  = $data['updated_by'] ?? null;    

        if (!$id_mqf) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tmqf 
                    SET kod_mqf = :kodMQF, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE id_mqf = :idMQF";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':kodMQF' => $kod_mqf,
                ':updated_by' => $updated_by,
                ':idMQF'     => $id_mqf
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

    public function deleteDataMQF(array $data): bool 
    {
        $idMQF = $data['idMQF'] ?? null;
        $deleted_by = $data['deleted_by'] ?? null;
        $status_MQF = 0; // 0 = deleted

        if (!$idMQF) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();
  
            $sql = "UPDATE spk_tmqf 
                    SET status_aktif = :statusMQF, 
                        deleted_by = :deleted_by, 
                        deleted_date = NOW() 
                    WHERE id_mqf = :idMQF";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':statusMQF' => $status_MQF,
                ':deleted_by' => $deleted_by,
                ':idMQF' => $idMQF
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