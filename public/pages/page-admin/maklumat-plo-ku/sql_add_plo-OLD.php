<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}
$program_universiti = 'Universiti';
$sesiid = $_POST["txtsesiid"];
$programid = $_POST["txtprogramid"];
$kodplo = $_POST["selectkodplo"];
$kodmqf = $_POST["selectkodmqf"];
$keteranganbm = $_POST["txtketeranganplo"];
$created_by = $_SESSION['id_staf'];

$sql = "insert into spk_tplo(program_universiti, kod_plo, keterangan_bm, kod_sesi, kod_mqf, created_by, created_date) values ('$program_universiti', '$kodplo','$keteranganbm','$sesiid','$kodmqf','$created_by', GETDATE()) select @@identity as id_plo";
$sql_result = @sybase_query($sql, $connection);
$result = @sybase_fetch_array($sql_result);
$plo = $result['id_plo'];
if (!empty($_POST['chkpeo'])) {
    foreach ($_POST['chkpeo'] as $peo) {
        $sql = "insert into spk_tpenetapan_peo_plo (id_peo, id_plo, created_by, created_date) "
                . "values ($peo, $plo, '$created_by', GETDATE())";
        $sql_result = @sybase_query($sql, $connection);
        $result = @sybase_fetch_array($sql_result);
    }
}
if ($result) {
   header('Location: ../maklumat-plo-ku?action=save-success');
} else {
   header('Location: ../maklumat-plo-ku?action=save-fail');
}
?>