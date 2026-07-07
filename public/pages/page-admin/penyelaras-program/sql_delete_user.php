<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idstaf = $_POST["txtidstaf"];
//$deleted_by = $_SESSION['id_staf'];
$idrole = 2;

$sql = "delete from spk_tpenetapan_login_role where id_staf = '$idstaf' and id_role = $idrole";
$result = @sybase_query($sql, $connection);
echo $sql;

if ($result) {
  header('Location: ../penyelaras-program?action=delete-success');
} else {
  header('Location: ../penyelaras-program?action=delete-fail');
}
?>