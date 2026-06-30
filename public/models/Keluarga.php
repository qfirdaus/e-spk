<?php

class Keluarga
{
    private PDO $dbStudent;
    private PDO $dbEhepa;
    private User $userModel;

    public function __construct(PDO $pdoStudent, PDO $pdoEhepa, User $userModel)
    {
        $this->dbStudent = $pdoStudent;
        $this->dbEhepa = $pdoEhepa;
        $this->userModel = $userModel;
    }

    public function getFamilySAPDetails(string $matrik): ?array
    {
        $sql = "SELECT a.*, 
                       b.f015keterangan as warganegara_desc,
                       c.f015keterangan as negeri_lahir,
                       d.f021keterangan as status_kahwin,
                       e.f005sesi as semester_terkini,
                       f.f005sesi as semester_masuk,
                       g.f005sesi as semester_tamat
                FROM v210_eHEPA a
                LEFT JOIN t015kewarganegaraan b ON a.kewarganegaraan = b.f015kdnegeri
                LEFT JOIN t015negeri c ON a.neglahir = c.f015kdnegeri
                LEFT JOIN t021kahwin d ON a.kdkahwin = d.f021kdkahwin
                LEFT JOIN v005term e ON a.semsemasa = e.f005term
                LEFT JOIN v005term f ON a.sesimasuk = f.f005term
                LEFT JOIN v005term g ON a.sesitamat = g.f005term                
                WHERE convert(varchar(50), a.matrik) = :matrik
                  AND upper(convert(varchar(20), a.statuskategori)) = 'AKTIF'";

        $stmt = $this->dbStudent->prepare($sql);
        $stmt->execute(['matrik' => $matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getFatherDetails(string $matrik): ?array
    {
        $sql = "SELECT *
                FROM hepa_m_father_details             
                WHERE matric_no = ?";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute([$matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }  

    public function getMotherDetails(string $matrik): ?array
    {
        $sql = "SELECT *
                FROM hepa_m_mother_details             
                WHERE matric_no = ?";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute([$matrik]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }    

    public function getKategoriOKULookup(): array
    {
        $sql = "
            SELECT id, OKU_code, OKU_desc
            FROM lp_oku_categories
            ORDER BY OKU_desc ASC
        ";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }  
    
    public function getResidenceCategoryLookup(): array
    {
        $sql = "
            SELECT id, residence_code, residence_desc
            FROM lp_residence_categories
            ORDER BY residence_code ASC
        ";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);  
    }

    public function getNegeriLookup(): array
    {
        $sql = "
            SELECT id, state_code, state
            FROM lp_state
            WHERE country_code = 'M01'
            ORDER BY state ASC
        ";

        $stmt = $this->dbEhepa->prepare($sql);
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

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    public function getEmploymentStatusLookup(): array
    {
        
        $sql = "
                SELECT * 
                FROM lp_employment_status
        ";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }     

    public function getEmploymentSectorLookup(): array
    {
        $sql = "
                SELECT * 
                FROM lp_employment_sector
        ";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }

    public Function getUniformServiceLookup(): array
    {
        $sql = "
                SELECT * 
                FROM lp_uniform_service_type
        ";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }

    public Function getUniformServiceStatusLookup(): array
    {
        $sql = "
                SELECT * 
                FROM lp_uniform_service_status
        ";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public Function getSalaryRangeLookup(): array
    {
        $sql = "
                SELECT * 
                FROM lp_salary
        ";

        $stmt = $this->dbEhepa->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; //fetch all rows

        $salaryRanges = [];
        foreach ($rows as $row) {
            $min = (int)$row['min_salary'];
            $max = isset($row['max_salary']) ? (int)$row['max_salary'] : null;

            // Buat label untuk dropdown
            if ($min == 0 && $max !== null) {
                $label = '< RM' . $max;
            } elseif ($max === null) {
                $label = '> RM' . $min;
            } else {
                $label = 'RM' . $min . ' - RM' . $max;
            }

            $salaryRanges[] = [
                'value' => $min, // submit min_salary sebagai value
                'label' => $label
            ];
        }

        return $salaryRanges;        
    }

    //submit form
    public function saveDataBapa($user_id, $formData)
    {
        $sql = "
            INSERT INTO hepa_m_father_details (
                matric_no, father_name, ic_no, passport_no, phone_no, 
                email, health_status, document_path, dependents_count, highest_education,
                residence_category, address1, address2, address3, address4, state_code, country_code,
                employment_status, employment_sector, is_uniform_service, uniform_service_type, uniform_service_others, uniform_service_status, gross_monthly_income, income_proof_docpath,
                employer_name, employer_address1, employer_address2, employer_address3, employer_address4, employer_state_code, employer_country_code,
                created_at, created_by, updated_at, updated_by
            )
            VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                NOW(), ?, NULL, NULL
            )
            ON DUPLICATE KEY UPDATE
                father_name       = VALUES(father_name),
                ic_no             = VALUES(ic_no),
                passport_no       = VALUES(passport_no),
                phone_no          = VALUES(phone_no),
                email             = VALUES(email),
                health_status     = VALUES(health_status),
                document_path     = IFNULL(VALUES(document_path), document_path), -- Kekalkan fail lama jika tiada fail baru
                dependents_count  = VALUES(dependents_count),
                highest_education = VALUES(highest_education),
                residence_category = VALUES(residence_category),
                address1          = VALUES(address1),
                address2          = VALUES(address2),
                address3          = VALUES(address3),
                address4          = VALUES(address4),
                state_code        = VALUES(state_code),
                country_code      = VALUES(country_code),
                employment_status = VALUES(employment_status),
                employment_sector = VALUES(employment_sector),
                is_uniform_service = VALUES(is_uniform_service),
                uniform_service_type = VALUES(uniform_service_type),
                uniform_service_others = VALUES(uniform_service_others),
                uniform_service_status = VALUES(uniform_service_status),
                gross_monthly_income = VALUES(gross_monthly_income),
                income_proof_docpath = IFNULL(VALUES(income_proof_docpath), income_proof_docpath),
                employer_name = VALUES(employer_name),
                employer_address1 = VALUES(employer_address1),
                employer_address2 = VALUES(employer_address2),
                employer_address3 = VALUES(employer_address3),
                employer_address4 = VALUES(employer_address4),
                employer_state_code = VALUES(employer_state_code),
                employer_country_code = VALUES(employer_country_code),
                updated_at        = NOW(), 
                updated_by        = ?      
        ";

        $stmt = $this->dbEhepa->prepare($sql);

        return $stmt->execute([
            //insert
            $user_id,                                                    
            $formData['nama_ibu'] ?? '',                               
            !empty($formData['no_ic']) ? $formData['no_ic'] : null,      
            !empty($formData['no_passport']) ? $formData['no_passport'] : null, 
            $formData['no_telefon'] ?? null,                               
            $formData['emel'] ?? null,                                  
            $formData['status_kesihatan'] ?? null,                          
            $formData['dokumen_oku'] ?? null,                            
            isset($formData['bil_tanggungan']) ? (int)$formData['bil_tanggungan'] : 0, 
            $formData['tahap_pendidikan'] ?? null,   
            $formData['kategori_tempat_tinggal'] ?? null,                    
            $formData['alamat_ibu1'] ?? null,                              
            $formData['alamat_ibu2'] ?? null,                              
            $formData['alamat_ibu3'] ?? null,                              
            $formData['alamat_ibu4'] ?? null,                              
            $formData['negeri_ibu'] ?? null,                                
            $formData['negara_ibu'] ?? null, 
            $formData['status_pekerjaan_ibu'] ?? null,
            $formData['sektor_pekerjaan_ibu'] ?? null,
            $formData['perkhidmatan_beruniform_ibu'] ?? null,
            $formData['jenis_perkhidmatan_beruniform_ibu'] ?? null,
            $formData['perkhidmatan_beruniform_lain_ibu'] ?? null,
            $formData['status_perkhidmatan_beruniform_ibu'] ?? null,
            $formData['pendapatan_bulanan_ibu'] ?? 0.00,
            $formData['dokumen_income'] ?? null, 
            $formData['majikan'] ?? null,
            $formData['alamat_majikan1'] ?? null,   
            $formData['alamat_majikan2'] ?? null,   
            $formData['alamat_majikan3'] ?? null,   
            $formData['alamat_majikan4'] ?? null,   
            $formData['negeri_majikan'] ?? null,     
            $formData['negara_majikan'] ?? null,                 
            $user_id,                                                   

            // update ; updated_by
            $user_id                                                     
        ]);
    }

    public function saveDataIbu($user_id, $formData)
    {
        $sql = "
            INSERT INTO hepa_m_mother_details (
                matric_no, mother_name, ic_no, passport_no, phone_no, 
                email, health_status, document_path, dependents_count, highest_education,
                residence_category, address1, address2, address3, address4, state_code, country_code,
                employment_status, employment_sector, is_uniform_service, uniform_service_type, uniform_service_others, uniform_service_status, gross_monthly_income, income_proof_docpath,
                employer_name, employer_address1, employer_address2, employer_address3, employer_address4, employer_state_code, employer_country_code,
                created_at, created_by, updated_at, updated_by
            )
            VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                NOW(), ?, NULL, NULL
            )
            ON DUPLICATE KEY UPDATE
                mother_name       = VALUES(mother_name),
                ic_no             = VALUES(ic_no),
                passport_no       = VALUES(passport_no),
                phone_no          = VALUES(phone_no),
                email             = VALUES(email),
                health_status     = VALUES(health_status),
                document_path     = IFNULL(VALUES(document_path), document_path), -- Kekalkan fail lama jika tiada fail baru
                dependents_count  = VALUES(dependents_count),
                highest_education = VALUES(highest_education),
                residence_category = VALUES(residence_category),
                address1          = VALUES(address1),
                address2          = VALUES(address2),
                address3          = VALUES(address3),
                address4          = VALUES(address4),
                state_code        = VALUES(state_code),
                country_code      = VALUES(country_code),
                employment_status = VALUES(employment_status),
                employment_sector = VALUES(employment_sector),
                is_uniform_service = VALUES(is_uniform_service),
                uniform_service_type = VALUES(uniform_service_type),
                uniform_service_others = VALUES(uniform_service_others),
                uniform_service_status = VALUES(uniform_service_status),
                gross_monthly_income = VALUES(gross_monthly_income),
                income_proof_docpath = IFNULL(VALUES(income_proof_docpath), income_proof_docpath),
                employer_name = VALUES(employer_name),
                employer_address1 = VALUES(employer_address1),
                employer_address2 = VALUES(employer_address2),
                employer_address3 = VALUES(employer_address3),
                employer_address4 = VALUES(employer_address4),
                employer_state_code = VALUES(employer_state_code),
                employer_country_code = VALUES(employer_country_code),
                updated_at        = NOW(), 
                updated_by        = ?      
        ";

        $stmt = $this->dbEhepa->prepare($sql);

        return $stmt->execute([
            //insert
            $user_id,                                                    
            $formData['nama_ibu'] ?? '',                               
            !empty($formData['no_ic']) ? $formData['no_ic'] : null,      
            !empty($formData['no_passport']) ? $formData['no_passport'] : null, 
            $formData['no_telefon'] ?? null,                               
            $formData['emel'] ?? null,                                  
            $formData['status_kesihatan'] ?? null,                          
            $formData['dokumen_oku'] ?? null,                            
            isset($formData['bil_tanggungan']) ? (int)$formData['bil_tanggungan'] : 0, 
            $formData['tahap_pendidikan'] ?? null,   
            $formData['kategori_tempat_tinggal'] ?? null,                    
            $formData['alamat_ibu1'] ?? null,                              
            $formData['alamat_ibu2'] ?? null,                              
            $formData['alamat_ibu3'] ?? null,                              
            $formData['alamat_ibu4'] ?? null,                              
            $formData['negeri_ibu'] ?? null,                                
            $formData['negara_ibu'] ?? null, 
            $formData['status_pekerjaan_ibu'] ?? null,
            $formData['sektor_pekerjaan_ibu'] ?? null,
            $formData['perkhidmatan_beruniform_ibu'] ?? null,
            $formData['jenis_perkhidmatan_beruniform_ibu'] ?? null,
            $formData['perkhidmatan_beruniform_lain_ibu'] ?? null,
            $formData['status_perkhidmatan_beruniform_ibu'] ?? null,
            $formData['pendapatan_bulanan_ibu'] ?? 0.00,
            $formData['dokumen_income'] ?? null, 
            $formData['majikan'] ?? null,
            $formData['alamat_majikan1'] ?? null,   
            $formData['alamat_majikan2'] ?? null,   
            $formData['alamat_majikan3'] ?? null,   
            $formData['alamat_majikan4'] ?? null,   
            $formData['negeri_majikan'] ?? null,     
            $formData['negara_majikan'] ?? null,                 
            $user_id,                                                   

            // update ; updated_by
            $user_id                                                     
        ]);
    }    

}