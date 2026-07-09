<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$loc = $_POST["txtloc"];
$created_by = $_SESSION['id_staf'];

$sql = "insert into spk_tloc(loc, created_by, created_date) values ('$loc','$created_by', GETDATE())";
$result = @sybase_query($sql, $connection);
//echo $sql;
if ($result) {
   header('Location: ../loc?action=save-success');
} else {
   header('Location: ../loc?action=save-fail');
}
?>