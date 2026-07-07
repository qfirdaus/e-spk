<?php
include("../action/config.php");

//Retrieve User Info
$sql = "select * from spk_tlogin login
join spk_trole st on login.id_role = st.id_role
join stafdb.dbo.v630staf_service_skim_aktif staf on staf.nopekerja = login.id_staf
where id_login=".$_SESSION['id_login'];

$sql_result = @sybase_query($sql, $connection);
$row = @sybase_fetch_array($sql_result);

$role = $row ["role_sys"];
$id_staf = $row ["idpekerja"];
$no_staf = $row ["nopekerja"];
$nama_staf = $row ["gelar_nama"];
$jabatan = $row ["jabatanhakiki"];
?>
