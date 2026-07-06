<?php
class MaklumatPLO
{
    private PDO $dbSPK;
    private PDO $dbStudent;

    public function __construct(PDO $pdoSPK, PDO $pdoStudent)
    {
        $this->dbSPK = $pdoSPK;
        $this->dbStudent = $pdoStudent;
    }

    public function getListDataPLO(): array
    {
        $sql = "
            SELECT * 
            FROM spk_tplo 
            WHERE status_aktif= ?
        ";

        $stmt = $this->dbSPK->prepare($sql);
        $stmt->execute([1]);
        //$stmt->execute([1, $_SESSION["sesiplo"], $programuniversiti]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }     

    public function getSesiKemasukanLookup(): array
    {
        $sql = "
            SELECT * 
            FROM v005_spk 
            ORDER BY f005term desc
        ";

        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }      

    // public function getApplicationSessionLookup($config_type): array
    // {
    //     $sql = "
    //         SELECT a.*, b.award_desc
    //         FROM istar_config_date a
    //         LEFT JOIN lp_award_category b ON a.config_category_award = b.award_category
    //         WHERE config_type = ?
    //         ORDER BY a.id ASC
    //         LIMIT 1
    //     ";

    //     $stmt = $this->dbSPK->prepare($sql);

    //     $stmt->execute([$config_type]);

    //     return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    // }

    // public function saveDateApply($userID, $formData)
    // {      
    //     $sql = "
    //         INSERT INTO istar_config_date (
    //             config_type,
    //             config_category_award,
    //             config_name,
    //             start_date,
    //             end_date,
    //             created_at,
    //             created_by
    //         )
    //         VALUES (
    //             ?, ?, ?, ?, ?, NOW(), ?
    //         )
    //     ";

    //     $stmt = $this->dbSPK->prepare($sql);

    //     $stmt->execute([
    //         $formData['config_type'] ?? '',
    //         $formData['config_category_award'] ?? '',
    //         $formData['config_name_session'] ?? '',
    //         $this->toMysqlDate($formData['config_tarikh_mula'] ?? '' ?? null),
    //         $this->toMysqlDate($formData['config_tarikh_tamat'] ?? '' ?? null),
    //         $userID ?? ''
    //     ]);

    //     return true;
    // }
    
    // public function updateDateApply($record_id, $draft)
    // {
    //     try {

    //         $sql = "
    //             UPDATE istar_config_date
    //             SET
    //                 config_type = ?,
    //                 config_category_award = ?,
    //                 config_name = ?,
    //                 start_date = ?,
    //                 end_date = ?,
    //                 is_active_override = ?,
    //                 updated_at = NOW(),
    //                 updated_by = ?
    //             WHERE id = ?
    //         ";

    //         $stmt = $this->dbSPK->prepare($sql);

    //         $stmt->execute([

    //             $draft['config_type'] ?? '',
    //             $draft['config_category_award'] ?? '',
    //             $draft['config_name'] ?? '',
    //             $this->toMysqlDate($draft['start_date'] ?? null),
    //             $this->toMysqlDate($draft['end_date'] ?? null),
    //             $draft['is_active'] ?? 1,
    //             $draft['updated_by'] ?? '',
    //             $record_id
    //         ]);

    //         return true;

    //     } catch (Exception $e) {
    //         return false;
    //     }
    // }
    
    // public function deleteDateApply($rowID)
    // {
    //     try {

    //         $sql = "
    //             DELETE FROM istar_config_date
    //             WHERE id = ?
    //             LIMIT 1
    //         ";

    //         $stmt = $this->dbSPK->prepare($sql);
    //         $stmt->execute([$rowID]);

    //         return $stmt->rowCount() > 0;

    //     } catch (Exception $e) {
    //         return false;
    //     }
    // }

    function toMysqlDate($date)
    {
        if (!$date) return null;

        $d = DateTime::createFromFormat('d-m-Y', $date);
        return $d ? $d->format('Ymd') : null;
    }      

}
