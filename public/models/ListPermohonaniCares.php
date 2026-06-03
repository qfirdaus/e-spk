<?php
class ListPermohonaniCares
{
    private PDO $ehepa;

    public function __construct(PDO $ehepa)
    {
        $this->ehepa = $ehepa;
    }

    public function getListPengesahanPelajar($matrik): array
    {
        $sql = "
            SELECT * 
            FROM icare_apply_student_verify
            WHERE no_matrik = ?
            ORDER BY id DESC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute([$matrik]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }     
}
