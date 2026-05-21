<?php

class Penglibatan
{
    private PDO $istad;
    private PDO $ehepa;

    public function __construct(PDO $istad, PDO $ehepa)
    {
        $this->istad = $istad;
        $this->ehepa = $ehepa;
    }

    public function getAllKegiatan(string $matrik): array
    {
        //peserta kegiatan pelajar
        $sql = "
            SELECT
                id_kegiatan_pelajar,
                matrik,
                'PESERTA' AS pencapaian,
                'IStAD' AS sumber,
                nama_kegiatan_pelajar AS nama,
                tarikh_mula AS tarikh,
                tarikh_tamat,
                kod_sesi,
                NULL AS wakil,
                NULL AS peringkat                
            FROM v_kehadiran_kegiatan_pelajar
            WHERE nama_kegiatan_pelajar IS NOT NULL AND matrik = :matrik
        ";

        $stmt = $this->istad->prepare($sql);

        $stmt->execute(['matrik' => $matrik]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllJawatan(string $matrik): array
    {
        //jawatan kegiatan pelajar & badan pelajar
        $sql = "
            SELECT
                nostaf_matrik AS matrik,
                id_jawatan,
                jawatan,
                'IStAD' AS sumber,
                id_kategori_aktiviti,
                kod_kategori_aktiviti,
                kategori_aktiviti,
                id_kegiatan_pelajar AS id_kegiatan_badan,
                nama_kegiatan_pelajar AS nama_bp_program,
                tarikh_mula,
                tarikh_tamat,
                kod_sesi
            FROM v_ahli_kegiatan_pelajar
            WHERE nostaf_matrik = :matrik1

            UNION

            SELECT
                matrik,
                id_jawatan,
                jawatan,
                'IStAD' AS sumber,
                id_kategori_aktiviti,
                kod_kategori_aktiviti,
                kategori_aktiviti,                
                id_ahli_bp AS id_kegiatan_bp,
                nama_badan_pelajar AS nama_bp_program,
                tarikh_mula,
                tarikh_tamat,
                kod_sesi
            FROM v_ahli_badan_pelajar
            WHERE matrik = :matrik2
        ";

        $stmt = $this->istad->prepare($sql);

       $stmt->execute(['matrik1' => $matrik, 'matrik2' => $matrik]);    

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function getPeringkatLookup(): array
    {
        $sql = "
            SELECT idperingkat, peringkat_code, peringkat_my
            FROM lp_level
            ORDER BY idperingkat ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPencapaianLookup(): array
    {
        $sql = "
            SELECT idpencapaian, pencapaian_code, pencapaian_my
            FROM lp_achievement
            ORDER BY idpencapaian ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getJawatanLookup(): array
    {
        $sql = "
            SELECT id_jawatan, keterangan, keteranganBP
            FROM tbl_jawatan
            ORDER BY sort ASC
        ";

        $stmt = $this->istad->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
    /**  Get lookup data   */

    public function testConnection(): bool
    {
        $stmt = $this->istad->query("SELECT 1");
        return (bool) $stmt->fetchColumn();
    }
}