<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idpenilaian = $_POST["txtidpenilaian"];
$penilaian = $_POST["txtpenilaian"];
$updated_by = $_SESSION['id_staf'];

$sql = "update spk_tpenilaian set penilaian='$penilaian', updated_by='$updated_by', updated_date=GETDATE() where id_penilaian = $idpenilaian";
$result = @sybase_query($sql, $connection);

if ($result) {
   header('Location: ../penilaian?action=save-success');
} else {
   header('Location: ../penilaian?action=save-fail');
}
?>