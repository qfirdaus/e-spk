<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$kaedah_pengajaran = $_POST["txtkaedah_pengajaran"];
$created_by = $_SESSION['id_staf'];

$sql = "insert into spk_tkaedah_pengajaran(kaedah_pengajaran, created_by, created_date) values ('$kaedah_pengajaran','$created_by', GETDATE())";
$result = @sybase_query($sql, $connection);
//echo $sql;
if ($result) {
   header('Location: ../kaedah-pengajaran?action=save-success');
} else {
   header('Location: ../kaedah-pengajaran?action=save-fail');
}
?>