<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idloc = $_POST["txtidloc"];
$deleted_by = $_SESSION['id_staf'];

$sql = "update spk_tloc set status_aktif=0, deleted_by='$deleted_by', deleted_date=GETDATE() where id_loc = $idloc";
$result = @sybase_query($sql, $connection);

if ($result) {
  header('Location: ../loc?action=delete-success');
} else {
  header('Location: ../loc?action=delete-fail');
}
?>

