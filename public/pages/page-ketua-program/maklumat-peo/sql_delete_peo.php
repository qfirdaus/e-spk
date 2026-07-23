<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idpeo = $_POST["txtidpeo"];
$deleted_by = $_SESSION['id_staf'];

$sql = "update spk_tpeo set status_aktif=0, deleted_by='$deleted_by', deleted_date=GETDATE() where id_peo = $idpeo";
$result = @sybase_query($sql, $connection);

if ($result) {
   header('Location: ../maklumat-peo?action=delete-success');
} else {
   header('Location: ../maklumat-peo?action=delete-fail');
}
?>