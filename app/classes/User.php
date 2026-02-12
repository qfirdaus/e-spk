<?php
// classes/User.php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * ✅ Model untuk pengurusan pengguna sistem e-Prestasi (MySQL)
 */
class User extends BaseModel
{
    /** ✅ Cari pengguna ikut f_stafID (diguna semasa login) */
    public function findByStafID(string $f_stafID): ?array
    {
        $sql = "SELECT f_stafID, f_password, f_nama, f_nickname, f_nopekerja, f_groupID, f_groupKod, f_flag
                FROM tbl_m_user
                WHERE f_stafID = :sid
                LIMIT 1";
        return $this->fetchOne($sql, [':sid' => $f_stafID]);
    }

    /** ✅ Ambil maklumat penuh pengguna berdasarkan f_stafID */
    public function getProfile(string $f_stafID = ''): ?array
    {
        // Jika tak diberi, fallback ke session login
        $sid = $f_stafID !== '' ? $f_stafID : ($_SESSION['f_stafID'] ?? '');
        if ($sid === '') return null;

        $sql = "SELECT 
                    u.f_userID,
                    u.f_stafID,
                    u.f_nopekerja,
                    u.f_nama,
                    u.f_nickname,
                    u.f_groupID,
                    u.f_groupKod,
                    u.f_themeSetting,
                    g.f_groupName
                FROM tbl_m_user u
                LEFT JOIN tbl_m_group g ON u.f_groupID = g.f_groupID
                WHERE u.f_stafID = :sid
                  AND u.f_statusID != 9
                LIMIT 1";
        return $this->fetchOne($sql, [':sid' => $sid]);
    }

    /** ✅ Jana URL avatar staf berdasarkan f_nopekerja (numeric) */
    public function getAvatarUrl(?string $f_nopekerja): string
    {
        return $f_nopekerja
            ? "https://esmartcard.upnm.edu.my/img/staf/{$f_nopekerja}.jpg"
            : base_url('assets/images/no-image.jpg');
    }
    

    /** ✅ Dapatkan semua pengguna dalam group tertentu (guna groupID sahaja) */
    public function getAllUsers(int $groupId = 0): array
    {
        if ($groupId <= 0) {
            return [];
        }

        $sql = "SELECT f_userID, f_stafID, f_nopekerja, f_nama, f_jawatan, f_groupID, f_groupKod, f_status
                FROM tbl_m_user
                WHERE f_groupID = :gid
                ORDER BY f_nama ASC, f_userID ASC";
        return $this->fetchAll($sql, [':gid' => $groupId]);
    }

    /** ✅ Label peranan user (guna f_groupKod) */
    public function getRoleLabel(?array $profile = null): string
    {
        $prof = $profile ?? $this->getProfile();
        return (string)($prof['f_groupKod'] ?? '');
    }

    /** (Opsyen) Update tema pengguna */
    public function updateTheme(string $f_stafID, array $theme): bool
    {
        $sql = "UPDATE tbl_m_user SET f_themeSetting = :t WHERE f_stafID = :sid LIMIT 1";
        return $this->execute($sql, [
            ':t'   => json_encode($theme, JSON_UNESCAPED_UNICODE),
            ':sid' => $f_stafID
        ]) > 0;
    }
}
