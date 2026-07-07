<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idmqf = $_POST["txtidmqf"];
$mqf = $_POST["txtmqf"];
$updated_by = $_SESSION['id_staf'];

$sql = "update spk_tmqf set kod_mqf='$mqf', updated_by='$updated_by', updated_date=GETDATE() where id_mqf = $idmqf";
$result = @sybase_query($sql, $connection);

if ($result) {
   header('Location: ../MQF?action=save-success');
} else {
   header('Location: ../MQF?action=save-fail');
}
?>