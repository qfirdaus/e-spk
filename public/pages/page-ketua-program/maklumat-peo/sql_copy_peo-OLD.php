<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$tosesi = $_POST["txtsesi"];
$toprogramid = $_POST["txtprogramid"];
$fromsesiid = $_POST["selectSesiModal"];
$fromprogramid = $_POST["selectProgramModal"];
$created_by = $_SESSION['id_staf'];

//Selected Sesi
$sql = "select * from v005_spk where sesi2='$tosesi'";
$sql_result_term = @sybase_query($sql, $connection);
$row = @sybase_fetch_array($sql_result_term);
$tosesiid = substr($row["f005term"], 0, -1);


$sql = "select * from spk_tpeo where status_aktif=1 and sesi='$fromsesiid' and kod_program='$fromprogramid'";
$sql_result = @sybase_query($sql, $connection);
$result_peo = null;
// echo $sql;
while ($result = @sybase_fetch_array($sql_result)) {
    $sql_peo = "insert into spk_tpeo(kod_peo, keterangan_bm, tarikh_senat, kod_sesi, sesi, kod_jabatan, kod_program, created_by, created_date) "
            . "values ('".$result['kod_peo']."','".$result['keterangan_bm']."','".$result['tarikh_senat']."','$tosesiid','$tosesi','".$_SESSION['ptj']."','$toprogramid','$created_by', GETDATE())";
    $result_peo = @sybase_query($sql_peo, $connection);
    //echo $sql_peo;
}


if ($result_peo) {
    header('Location: ../maklumat-peo?action=save-success');
} else {
    header('Location: ../maklumat-peo?action=save-fail');
}
?>