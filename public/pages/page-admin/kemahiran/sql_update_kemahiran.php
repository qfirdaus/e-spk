<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$idkemahiran = $_POST["txtidkemahiran"];
$kemahiran = $_POST["txtkemahiran"];
$updated_by = $_SESSION['id_staf'];

$sql = "update spk_tkemahiran set kemahiran='$kemahiran', updated_by='$updated_by', updated_date=GETDATE() where id_kemahiran = $idkemahiran";
$result = @sybase_query($sql, $connection);

if ($result) {
   header('Location: ../kemahiran?action=save-success');
} else {
   header('Location: ../kemahiran?action=save-fail');
}
?>