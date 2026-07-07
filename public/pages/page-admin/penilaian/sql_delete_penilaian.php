<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idpenilaian = $_POST["txtidpenilaian"];
$deleted_by = $_SESSION['id_staf'];

$sql = "update spk_tpenilaian set status_aktif=0, deleted_by='$deleted_by', deleted_date=GETDATE() where id_penilaian = $idpenilaian";
$result = @sybase_query($sql, $connection);

if ($result) {
  header('Location: ../penilaian?action=delete-success');
} else {
  header('Location: ../penilaian?action=delete-fail');
}
?>

