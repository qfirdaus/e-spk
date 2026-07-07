<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idkemahiran = $_POST["txtidkemahiran"];
$deleted_by = $_SESSION['id_staf'];

$sql = "update spk_tkemahiran set status_aktif=0, deleted_by='$deleted_by', deleted_date=GETDATE() where id_kemahiran = $idkemahiran";
$result = @sybase_query($sql, $connection);

if ($result) {
  header('Location: ../kemahiran?action=delete-success');
} else {
  header('Location: ../kemahiran?action=delete-fail');
}
?>

