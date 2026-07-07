<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$kemahiran = $_POST["txtkemahiran"];
$created_by = $_SESSION['id_staf'];

$sql = "insert into spk_tkemahiran(kemahiran, created_by, created_date) values ('$kemahiran','$created_by', GETDATE())";
$result = @sybase_query($sql, $connection);
//echo $sql;
if ($result) {
   header('Location: ../kemahiran?action=save-success');
} else {
   header('Location: ../kemahiran?action=save-fail');
}
?>