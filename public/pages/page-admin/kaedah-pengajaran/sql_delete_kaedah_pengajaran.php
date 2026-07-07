<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idkaedah_pengajaran = $_POST["txtidkaedah_pengajaran"];
$deleted_by = $_SESSION['id_staf'];

$sql = "update spk_tkaedah_pengajaran set status_aktif=0, deleted_by='$deleted_by', deleted_date=GETDATE() where id_kaedah_pengajaran = $idkaedah_pengajaran";
$result = @sybase_query($sql, $connection);

if ($result) {
  header('Location: ../kaedah-pengajaran?action=delete-success');
} else {
  header('Location: ../kaedah-pengajaran?action=delete-fail');
}
?>

