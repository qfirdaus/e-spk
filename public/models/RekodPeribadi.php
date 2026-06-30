<?php

class RekodPeribadi
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

    public function getStatusKerjaLookup(): array
    {
        $sql = "
            SELECT id, emp_status_code, emp_status_my, emp_status_en
            FROM lp_employment_status
            ORDER BY id ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }

    public function getSektorKerjaLookup(): array
    {
        $sql = "
            SELECT id, emp_sector_code, emp_sector_my, emp_sector_en
            FROM lp_employment_sector
            ORDER BY id ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }    

    public function getKategoriOKULookup(): array
    {
        $sql = "
            SELECT id, OKU_code, OKU_desc
            FROM lp_oku_categories
            ORDER BY OKU_desc ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }  
    
    public function getBankLookup(): array
    {
        $sql = "
            SELECT id, bank_code, bank_name
            FROM lp_bank
            ORDER BY bank_name ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    } 
    
    public function getSponsorLookup(): array
    {
        $sql = "
            SELECT id, sponsor_code, sponsor_name
            FROM lp_sponsor
            ORDER BY order_by ASC
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }

    public function getDataPekerjaan($matrik): array
    {
        $sql = "
            SELECT * 
            FROM hepa_m_employment 
            WHERE matric_no = ?
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute([$matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC);        
    } 

    public function getDataKesihatan($matrik): array
    {
        $sql = "
            SELECT * 
            FROM hepa_m_health 
            WHERE matric_no = ?
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute([$matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC);          
    }

    public function getDataAkaun($matrik): array
    {
        $sql = "
            SELECT * 
            FROM hepa_m_account 
            WHERE matric_no = ?
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute([$matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC);          
    }

    public function getDataSponsor($matrik): array
    {
        $sql = "
            SELECT * 
            FROM hepa_m_sponsor
            WHERE matric_no = ?
        ";

        $stmt = $this->ehepa->prepare($sql);
        $stmt->execute([$matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC);          
    }

    public function savePekerjaan($user_id, $formData)
    {
        //tak perlu where masa update - matric_no dah set unique dalam table
        $sql = "
            INSERT INTO hepa_m_employment (
                matric_no, emp_status, emp_sector, parttime_status, parttime_type,
                address1, address2, address3, address4, state, country,
                created_at, created_by, updated_at, updated_by
            )
            VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, 
                NOW(), ?, NULL, NULL -- NULL dipaksa semasa INSERT
            )
            ON DUPLICATE KEY UPDATE
                emp_status       = VALUES(emp_status),
                emp_sector       = VALUES(emp_sector),
                parttime_status  = VALUES(parttime_status),
                parttime_type    = VALUES(parttime_type),
                address1         = VALUES(address1),
                address2         = VALUES(address2),
                address3         = VALUES(address3),
                address4         = VALUES(address4),
                state            = VALUES(state),
                country          = VALUES(country),
                updated_at       = NOW(), 
                updated_by       = ?      
        ";

        $stmt = $this->ehepa->prepare($sql);

        return $stmt->execute([
            $user_id,                                          
            $formData['status_pekerjaan'] ?? '',                
            $formData['sektor_pekerjaan'] ?? '',                
            $formData['status_pekerjaan_sambilan'] ?? '',       
            $formData['jenis_pekerjaan_sambilan'] ?? '',        
            $formData['alamat1'] ?? '',                         
            $formData['alamat2'] ?? '',                         
            $formData['poskod'] ?? '',                         
            $formData['bandar'] ?? '',                          
            $formData['negeri'] ?? '',                          
            $formData['negara'] ?? '',                          
            $user_id,                                           // created_by

            // --- ON DUPLICATE KEY UPDATE ---
            $user_id                                            // updated_by if dah ada dalam DB
        ]);
    }

    public function saveKesihatan($user_id, $formData)
    {
        $sql = "
            INSERT INTO hepa_m_health (
                matric_no, health_status, document_path,
                created_at, created_by, updated_at, updated_by
            )
            VALUES (
                ?, ?, ?, 
                NOW(), ?, NULL, NULL 
            )
            ON DUPLICATE KEY UPDATE
                health_status    = VALUES(health_status),
                document_path    = VALUES(document_path),
                updated_at       = NOW(), 
                updated_by       = ?      
        ";

        $stmt = $this->ehepa->prepare($sql);

        return $stmt->execute([
            $user_id,                                          
            $formData['status_kesihatan'] ?? '',                
            $formData['dokumen_oku'] ?? '',                  
            $user_id,                                           // created_by

            // --- ON DUPLICATE KEY UPDATE ---
            $user_id                                            // updated_by if dah ada dalam DB
        ]);
    }

    public function saveAkaun($user_id, $formData)
    {
        $sql = "
            INSERT INTO hepa_m_account (
                matric_no, bank_code, account_no, document_path,
                created_at, created_by, updated_at, updated_by
            )
            VALUES (
                ?, ?, ?, ?,
                NOW(), ?, NULL, NULL 
            )
            ON DUPLICATE KEY UPDATE
                bank_code    = VALUES(bank_code),
                account_no    = VALUES(account_no),
                document_path    = VALUES(document_path),
                updated_at       = NOW(), 
                updated_by       = ?      
        ";

        $stmt = $this->ehepa->prepare($sql);

        return $stmt->execute([
            $user_id,                                          
            $formData['kod_bank'] ?? '',    
            $formData['no_akaun'] ?? '',           
            $formData['dokumen_akaun'] ?? '',                  
            $user_id,                                           // created_by

            // --- ON DUPLICATE KEY UPDATE ---
            $user_id                                            // updated_by if dah ada dalam DB
        ]);
    }

    public function saveDataSponsor($user_id, $formData)
    {
        $sql = "
            INSERT INTO hepa_m_sponsor (
                matric_no, sponsor_code,
                created_at, created_by, updated_at, updated_by
            )
            VALUES (
                ?, ?,
                NOW(), ?, NULL, NULL
            )
            ON DUPLICATE KEY UPDATE
                sponsor_code      = VALUES(sponsor_code),
                updated_at        = NOW(), 
                updated_by        = ?      
        ";

        $stmt = $this->ehepa->prepare($sql);

        return $stmt->execute([
            //insert
            $user_id,                                                    
            $formData['pembiayaan_pengajian'] ?? '',             
            $user_id,                                                   

            // update ; updated_by
            $user_id                                                     
        ]);
    }    

    public function testConnection(): bool
    {
        $stmt = $this->ehepa->query("SELECT 1");
        return (bool) $stmt->fetchColumn();
    }        
}    