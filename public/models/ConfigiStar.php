<?php
class ConfigiStar
{
    private PDO $ehepa;

    public function __construct(PDO $ehepa)
    {
        $this->ehepa = $ehepa;
    }

    public function getListDateConfig($matrik): array
    {
        $sql = "
            SELECT a.*, lp.award_desc, case when is_active = 1 then 'Aktif' else 'Tidak Aktif' end as is_active_status
            FROM istar_config_date a
            LEFT JOIN lp_award_category lp ON a.config_category_award = lp.award_category
            ORDER BY a.id DESC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }  
     

    public function saveDateApply($configType, $sessionName, $draft)
    {
        $checkSql = "
            SELECT *
            FROM istar_config_date
            WHERE config_type = ?
            AND config_name = ?
            LIMIT 1
        ";

        $checkStmt = $this->ehepa->prepare($checkSql);
        $checkStmt->execute([$configType, $sessionName]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);        

        if ($existing) {
            return false;

        }else {        
            $sql = "
                INSERT INTO istar_config_date (
                    config_type,
                    config_category_award,
                    config_name,
                    start_date,
                    end_date,
                    is_active,
                    created_at,
                    created_by
                )
                VALUES (
                    ?, ?, ?, ?, ?, ?, NOW(), ?
                )
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([

                // dataStudent
                $draft['config_type'] ?? '',
                $draft['config_category_award'] ?? '',
                $draft['config_name'] ?? '',
                $this->toMysqlDate($draft['start_date'] ?? '' ?? null),
                $this->toMysqlDate($draft['end_date'] ?? '' ?? null),
                $draft['is_active'] ?? '',  
                $draft['created_by'] ?? ''
            ]);
        }   
        return true;
    }
    
    public function updateDateApply($record_id, $draft)
    {
        try {

            $sql = "
                UPDATE istar_config_date
                SET
                    config_type = ?,
                    config_category_award = ?,
                    config_name = ?,
                    start_date = ?,
                    end_date = ?,
                    is_active = ?,
                    updated_at = NOW(),
                    updated_by = ?
                WHERE id = ?
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([

                $draft['config_type'] ?? '',
                $draft['config_category_award'] ?? '',
                $draft['config_name'] ?? '',
                $this->toMysqlDate($draft['start_date'] ?? null),
                $this->toMysqlDate($draft['end_date'] ?? null),
                $draft['is_active'] ?? 1,
                $draft['updated_by'] ?? '',
                $record_id
            ]);

            return true;

        } catch (Exception $e) {
            return false;
        }
    }
    
    public function deleteDateApply($rowID)
    {
        try {

            $sql = "
                DELETE FROM istar_config_date
                WHERE id = ?
                LIMIT 1
            ";

            $stmt = $this->ehepa->prepare($sql);
            $stmt->execute([$rowID]);

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            return false;
        }
    }

    function toMysqlDate($date)
    {
        if (!$date) return null;

        $d = DateTime::createFromFormat('d-m-Y', $date);
        return $d ? $d->format('Ymd') : null;
    }      

}
