<?php
declare(strict_types=1);

class Penilaian
{
    private PDO $pdoSPK;
    private PDO $pdoStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->pdoSPK = $pdoSPK;
        $this->pdoStudent = $pdoStudent;
    }
    public function getAssessmentList(): array
    {       
        $sql = "SELECT * FROM spk_tpenilaian WHERE status_aktif=1 ORDER BY penilaian";
        $stmt = $this->pdoSPK->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // simpan Assessment baru
    public function addAssessmentBaharu(array $data): bool 
    {
        $penilaian = $data['txtpenilaian'] ?? null;
        $created_by = $data['created_by'] ?? null;

        try {
            $this->pdoSPK->beginTransaction();
           
            $sql = "INSERT INTO spk_tpenilaian(penilaian, created_by, created_date)
                    VALUES (:penilaian, :created_by, NOW())";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            
            $stmt->execute([
                ':penilaian' => $penilaian,
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

    public function updateDataAssessment(array $data): bool 
    {
        $id_penilaian = $data['txtidpenilaian_edit'] ?? null;
        $penilaian   = $data['txtpenilaian_edit'] ?? null;
        $updated_by  = $data['updated_by'] ?? null;    

        if (!$id_penilaian) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();

            $sql = "UPDATE spk_tpenilaian 
                    SET penilaian = :penilaian, 
                        updated_by = :updated_by, 
                        updated_date = NOW() 
                    WHERE id_penilaian = :idPenilaian";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':penilaian' => $penilaian,
                ':updated_by' => $updated_by,
                ':idPenilaian'     => $id_penilaian
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

    public function deleteDataAssessment(array $data): bool 
    {
        $idPenilaian = $data['idPenilaian'] ?? null;
        $deleted_by = $data['deleted_by'] ?? null;
        $status_Assessment = 0; // 0 = deleted

        if (!$idPenilaian) {
            return false;
        }

        try {
            $this->pdoSPK->beginTransaction();
  
            $sql = "UPDATE spk_tpenilaian 
                    SET status_aktif = :statusAssessment, 
                        deleted_by = :deleted_by, 
                        deleted_date = NOW() 
                    WHERE id_penilaian = :idPenilaian";
                    
            $stmt = $this->pdoSPK->prepare($sql);
            $stmt->execute([
                ':statusAssessment' => $status_Assessment,
                ':deleted_by' => $deleted_by,
                ':idPenilaian' => $idPenilaian
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