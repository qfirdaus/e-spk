<?php

class PengesahanPelajar
{
    private PDO $ehepa;

    public function __construct(PDO $ehepa)
    {
        $this->ehepa = $ehepa;
    }

    /**  Get lookup data   */
    
    public function getNegeriLookup(): array
    {
        $sql = "
            SELECT id, state_code, state
            FROM lp_state
            WHERE country_code = 'M01'
            ORDER BY state ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }   

    public function getNegaraLookup(): array
    {
        $sql = "
            SELECT id, country_code, country
            FROM lp_country
            ORDER BY country ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function savePermohonan($user_id, $draft)
    {
        $checkSql = "
            SELECT id
            FROM icare_apply_student_verify
            WHERE submitted_by = ?
            AND status = 1
            LIMIT 1
        ";

        $checkStmt = $this->ehepa->prepare($checkSql);
        $checkStmt->execute([$user_id]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);        

        if ($existing) {

            $sql = "
                UPDATE icare_apply_student_verify SET
                    nama_pemohon = ?,
                    no_kp = ?,
                    no_matrik = ?,
                    emel = ?,
                    fakulti = ?,
                    program = ?,
                    semester = ?,
                    nama_penerima = ?,
                    alamat1 = ?,
                    alamat2 = ?,
                    poskod = ?,
                    bandar = ?,
                    negeri = ?,
                    negara = ?,
                    setuju = ?,
                    submitted_at = NOW()
                WHERE id = ?
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([
                $draft['dataStudent']['nama_penuh'] ?? '',
                $draft['dataStudent']['nokp'] ?? '',
                $draft['dataStudent']['matrik'] ?? '',
                $draft['dataStudent']['email'] ?? '',
                $draft['dataStudent']['kdfakulti'] ?? '',
                $draft['dataStudent']['program'] ?? '',
                $draft['dataStudent']['semester'] ?? '',

                $draft['penerima']['nama_penerima'] ?? '',
                $draft['penerima']['alamat1'] ?? '',
                $draft['penerima']['alamat2'] ?? '',
                $draft['penerima']['poskod'] ?? '',
                $draft['penerima']['bandar'] ?? '',
                $draft['penerima']['negeri'] ?? '',
                $draft['penerima']['negara'] ?? '',

                (
                    ($draft['perakuan']['chk1'] ?? 0) &&
                    ($draft['perakuan']['chk2'] ?? 0)
                ) ? 1 : 0,

                $existing['id']
            ]);

            return true;
        }else {        
            $sql = "
                INSERT INTO icare_apply_student_verify (
                    nama_pemohon,
                    no_kp,
                    no_matrik,
                    emel,
                    fakulti,
                    program,
                    semester,
                    nama_penerima,
                    alamat1,
                    alamat2,
                    poskod,
                    bandar,
                    negeri,
                    negara,
                    setuju,
                    status,
                    submitted_at,
                    submitted_by
                )
                VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?
                )
            ";

            $stmt = $this->ehepa->prepare($sql);

            $stmt->execute([

                // dataStudent
                $draft['dataStudent']['nama_penuh'] ?? '',
                $draft['dataStudent']['nokp'] ?? '',
                $draft['dataStudent']['matrik'] ?? '',
                $draft['dataStudent']['email'] ?? '',
                $draft['dataStudent']['kdfakulti'] ?? '',
                $draft['dataStudent']['program'] ?? '',
                $draft['dataStudent']['semester'] ?? '',

                // penerima
                $draft['penerima']['nama_penerima'] ?? '',
                $draft['penerima']['alamat1'] ?? '',
                $draft['penerima']['alamat2'] ?? '',
                $draft['penerima']['poskod'] ?? '',
                $draft['penerima']['bandar'] ?? '',
                $draft['penerima']['negeri'] ?? '',
                $draft['penerima']['negara'] ?? '',

                // perakuan
                (
                    ($draft['perakuan']['chk1'] ?? 0) &&
                    ($draft['perakuan']['chk2'] ?? 0)
                ) ? 1 : 0,

                // status : baru
                1,

                // submitted_by
                $user_id
            ]);
        }   
        return true;
    }  
    
    public function testConnection(): bool
    {
        $stmt = $this->ehepa->query("SELECT 1");
        return (bool) $stmt->fetchColumn();
    }        
}    