<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$mqf = $_POST["txtmqf"];
$created_by = $_SESSION['id_staf'];

$sql = "insert into spk_tmqf(kod_mqf, created_by, created_date) values ('$mqf','$created_by', GETDATE())";
$result = @sybase_query($sql, $connection);
//echo $sql;
if ($result) {
   header('Location: ../MQF?action=save-success');
} else {
   header('Location: ../MQF?action=save-fail');
}
?>