<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idkaedah_pengajaran = $_POST["txtidkaedah_pengajaran"];
$kaedah_pengajaran = $_POST["txtkaedah_pengajaran"];
$updated_by = $_SESSION['id_staf'];

$sql = "update spk_tkaedah_pengajaran set kaedah_pengajaran='$kaedah_pengajaran', updated_by='$updated_by', updated_date=GETDATE() where id_kaedah_pengajaran = $idkaedah_pengajaran";
$result = @sybase_query($sql, $connection);

if ($result) {
   header('Location: ../kaedah-pengajaran?action=save-success');
} else {
   header('Location: ../kaedah-pengajaran?action=save-fail');
}
?>