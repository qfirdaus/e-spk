<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$programuniversiti = 'Universiti';
$tosesiid = $_POST["txtsesi"];
//$toprogramid = $_POST["txtprogramid"];
$fromsesiid = $_POST["selectSesiModal"];
//$fromprogramid = $_POST["selectProgramModal"];
$created_by = $_SESSION['id_staf'];


$sql = "select * from spk_tplo where status_aktif=1 and kod_sesi='$fromsesiid' and program_universiti='$programuniversiti'";
$sql_result = @sybase_query($sql, $connection);
$result_peo = null;
// echo $sql;
while ($result = @sybase_fetch_array($sql_result)) {
    $sql_plo = "insert into spk_tplo(program_universiti, kod_plo, keterangan_bm, kod_sesi, created_by, created_date) "
            . "values ('$programuniversiti', '".$result['kod_plo']."','".$result['keterangan_bm']."','$tosesiid', '$created_by', GETDATE())";
    $result_peo = @sybase_query($sql_plo, $connection);
}


if ($result_peo) {
    header('Location: ../maklumat-plo-ku?action=save-success');
} else {
    header('Location: ../maklumat-plo-ku?action=save-fail');
}
?>