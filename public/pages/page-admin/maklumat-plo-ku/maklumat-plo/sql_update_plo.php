<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idplo = $_POST["txtidplo"];
$keteranganbm = $_POST["txtketeranganplo"];
$kodmqf = $_POST["selectkodmqf"];
$tarikhsenat = $_POST["txttarikhsenat"];
$updated_by = $_SESSION['id_staf'];

$sql = "update spk_tplo set keterangan_bm='$keteranganbm', kod_mqf = '$kodmqf', updated_by='$updated_by', updated_date=GETDATE() where id_plo = $idplo";
$result = @sybase_query($sql, $connection);

$sql = "delete from spk_tpenetapan_peo_plo where id_plo = $idplo";
$result = @sybase_query($sql, $connection);

if (!empty($_POST['chkpeo'])) {
    foreach ($_POST['chkpeo'] as $peo) {
        $sql = "insert into spk_tpenetapan_peo_plo (id_peo, id_plo, created_by, created_date) "
                . "values ($peo, $idplo, '$updated_by', GETDATE())";
        $sql_result = @sybase_query($sql, $connection);
        $result = @sybase_fetch_array($sql_result);
    }
}

if ($result) {
   header('Location: ../maklumat-plo?action=save-success');
} else {
   header('Location: ../maklumat-plo?action=save-fail');
}
?>