<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idpeo = $_POST["txtidpeo"];
$keteranganbm = $_POST["txtketeranganpeo"];
$tarikhsenat = $_POST["txttarikhsenat"];
$updated_by = $_SESSION['id_staf'];

$sql = "update spk_tpeo set keterangan_bm='$keteranganbm', tarikh_senat='$tarikhsenat', updated_by='$updated_by', updated_date=GETDATE() where id_peo = $idpeo";
$result = @sybase_query($sql, $connection);

if ($result) {
   header('Location: ../maklumat-peo?action=save-success');
} else {
   header('Location: ../maklumat-peo?action=save-fail');
}
?>