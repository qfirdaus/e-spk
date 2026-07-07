<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$penilaian = $_POST["txtpenilaian"];
$created_by = $_SESSION['id_staf'];

$sql = "insert into spk_tpenilaian(penilaian, created_by, created_date) values ('$penilaian','$created_by', GETDATE())";
$result = @sybase_query($sql, $connection);
//echo $sql;
if ($result) {
   header('Location: ../penilaian?action=save-success');
} else {
   header('Location: ../penilaian?action=save-fail');
}
?>