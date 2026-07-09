<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idloc = $_POST["txtidloc"];
$loc = $_POST["txtloc"];
$updated_by = $_SESSION['id_staf'];

$sql = "update spk_tloc set loc='$loc', updated_by='$updated_by', updated_date=GETDATE() where id_loc = $idloc";
$result = @sybase_query($sql, $connection);

if ($result) {
   header('Location: ../loc?action=save-success');
} else {
   header('Location: ../loc?action=save-fail');
}
?>