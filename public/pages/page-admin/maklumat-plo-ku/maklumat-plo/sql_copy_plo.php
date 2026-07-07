<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$tosesiid = $_POST["txtsesi"];
$toprogramid = $_POST["txtprogramid"];
$fromsesiid = $_POST["selectSesiModal"];
$fromprogramid = $_POST["selectProgramModal"];
$created_by = $_SESSION['id_staf'];


$sql = "select * from spk_tplo where status_aktif=1 and kod_sesi='$fromsesiid' and kod_program='$fromprogramid'";
$sql_result = @sybase_query($sql, $connection);
$result_peo = null;
// echo $sql;
while ($result = @sybase_fetch_array($sql_result)) {
    $sql_plo = "insert into spk_tplo(kod_plo, keterangan_bm, kod_sesi, kod_jabatan, kod_program, created_by, created_date) "
            . "values ('".$result['kod_plo']."','".$result['keterangan_bm']."','$tosesiid','".$_SESSION['ptj']."','$toprogramid','$created_by', GETDATE())";
    $result_peo = @sybase_query($sql_plo, $connection);
}


if ($result_peo) {
    header('Location: ../maklumat-plo?action=save-success');
} else {
    header('Location: ../maklumat-plo?action=save-fail');
}
?>