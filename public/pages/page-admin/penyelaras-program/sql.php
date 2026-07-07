<?php
include("../../action/config.php");

//Retrieve Penyelaras Jabatan
$sql = "select id_login, a.id_staf, gelar_nama, username, password, nama_jabatan, telefon_pej, email from spk_tlogin a
join stafdb.dbo.v630staf_service_skim_aktif b
on a.id_staf = b.nopekerja
join stafdb.dbo.v_senarai_FPJB fpjb on a.jabatan = fpjb.nama_singkat_jabatan
join spk_tpenetapan_login_role r on a.id_staf=r.id_staf
where r.id_role=2 and status_aktif=1";
$sql_result = @sybase_query($sql, $connection);


?>
