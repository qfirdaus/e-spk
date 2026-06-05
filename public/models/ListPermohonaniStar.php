<?php
class ListPermohonaniStar
{
    private PDO $ehepa;

    public function __construct(PDO $ehepa)
    {
        $this->ehepa = $ehepa;
    }

    public function getListMohonPingatGraduan($matrik): array
    {
        //table istar_application
        $sql = "
            SELECT 
                a.*,
                s.status AS status_name
            FROM istar_application a
            LEFT JOIN lp_status s 
                ON a.status = s.status_code
            WHERE a.matric_no = ?
            ORDER BY a.id DESC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute([$matrik]);

        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($applications)) {
            return [];
        }

        $ids = array_column($applications, 'id');

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // table istar_application_participation
        $sqlChild = "
            SELECT *
            FROM istar_application_participation
            WHERE application_id IN ($placeholders)
            ORDER BY id ASC
        ";

        $stmtChild = $this->ehepa->prepare($sqlChild);
        $stmtChild->execute($ids);

        $participations = $stmtChild->fetchAll(PDO::FETCH_ASSOC);

        // join child data to parent using application_id
        $grouped = [];

        foreach ($participations as $p) {
            $grouped[$p['application_id']][] = $p;
        }

        // ATTACH CHILD TO PARENT
        foreach ($applications as &$app) {
            $app['penglibatan'] = $grouped[$app['id']] ?? [];
        }

        return $applications;
    }  
     
}
