<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$nostaf = $_POST["txtnostaf"];
//$nokp = $_POST["txtnokp"];
$nokp = '123456';
$nama = $_POST["txtnama"];
$jabatan = $_POST["txtjabatansingkat"];
$idrole = 4;
$created_by = $_SESSION['id_staf'];

$sql = "select id_staf from spk_tlogin where id_staf='$nostaf'";
$sql_result = @sybase_query($sql, $connection);
$count = sybase_num_rows($sql_result);

if ($count == 0) {
	$sql = "insert into spk_tlogin(id_staf, nama, username, password, jabatan, created_by, created_date) values ('$nostaf','$nama','$nostaf', '$nokp', '$jabatan', '$created_by', GETDATE())";
	$result = @sybase_query($sql, $connection);
}

$sql = "select id_staf from spk_tpenetapan_login_role where id_staf='$nostaf' and id_role=$idrole";
$sql_result = @sybase_query($sql, $connection);
$count = sybase_num_rows($sql_result);

if ($count == 0) {
	$sql = "insert into spk_tpenetapan_login_role (id_staf, id_role, created_by, created_date) "
                . "values ('$nostaf', $idrole, '$created_by', GETDATE())"; 
    $sql_result = @sybase_query($sql, $connection);
}


if ($result) {
    header('Location: ../ketua-program?action=save-success');
} else {
   header('Location: ../ketua-program?action=save-fail');
}
?>