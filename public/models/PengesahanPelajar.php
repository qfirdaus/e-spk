<?php

class PengesahanPelajar
{
    private PDO $ehepa;

    public function __construct(PDO $ehepa)
    {
        $this->ehepa = $ehepa;
    }

    /**  Get lookup data   */
    public function getWakilLookup(): array
    {
        $sql = "
            SELECT idwakil, wakil_code, wakil_my
            FROM lp_representative
            ORDER BY idwakil ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function testConnection(): bool
    {
        $stmt = $this->ehepa->query("SELECT 1");
        return (bool) $stmt->fetchColumn();
    }        
}    