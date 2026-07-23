<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$sesiid = $_POST["txtsesiid"];
$sesi = $_POST["txtsesi"];
$programid = $_POST["txtprogramid"];
$kodpeo = $_POST["selectkodpeo"];
$keteranganbm = $_POST["txtketeranganpeo"];
$tarikhsenat = $_POST["txttarikhsenat"];
$created_by = $_SESSION['id_staf'];

$sql = "insert into spk_tpeo(kod_peo, keterangan_bm, tarikh_senat, kod_sesi, sesi, kod_jabatan, kod_program, created_by, created_date) values ('$kodpeo','$keteranganbm','$tarikhsenat','$sesiid','$sesi','" . $_SESSION['ptj'] . "','$programid', '$created_by', GETDATE())";
$result = @sybase_query($sql, $connection);
//echo $sql;
if ($result) {
   header('Location: ../maklumat-peo?action=save-success');
} else {
   header('Location: ../maklumat-peo?action=save-fail');
}
?>