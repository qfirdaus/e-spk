<?php

class Penglibatan
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllActive(string $matrik): array
    {
        $sql = "
        SELECT
            nostaf_matrik AS matrik,
            id_jawatan,
            nama_kegiatan_pelajar AS nama_program,
            tarikh_mula,
            tarikh_tamat,
            kod_sesi
        FROM v_ahli_kegiatan_pelajar
        WHERE nostaf_matrik = ?

        UNION

        SELECT
            matrik,
            id_jawatan,
            nama_badan_pelajar AS nama_program,
            tarikh_mula,
            tarikh_tamat,
            kod_sesi
        FROM v_ahli_badan_pelajar
        WHERE matrik = ?

        UNION

        SELECT
            matrik,
            7 AS id_jawatan,
            nama_kegiatan_pelajar AS nama_program,
            tarikh_mula,
            tarikh_tamat,
            kod_sesi
        FROM v_kehadiran_kegiatan_pelajar
        WHERE matrik = ?
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $matrik,
            $matrik,
            $matrik
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testConnection(): bool
    {
        $stmt = $this->db->query("SELECT 1");
        return (bool) $stmt->fetchColumn();
    }
}