<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$id_mqf = $_POST["txtidmqf"];
$deleted_by = $_SESSION['id_staf'];

$sql = "update spk_tmqf set status_aktif=0, deleted_by='$deleted_by', deleted_date=GETDATE() where id_mqf = $id_mqf";
$result = @sybase_query($sql, $connection);

if ($result) {
  header('Location: ../MQF?action=delete-success');
} else {
  header('Location: ../MQF?action=delete-fail');
}
?>

