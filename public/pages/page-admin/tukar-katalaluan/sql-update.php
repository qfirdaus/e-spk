<?php
include("../../action/config.php");

$idlogin = $_POST["txtidlogin"];
$oldpassword = $_POST["txtpasscurrent"];
$oldpassword1 = $_POST["txtpasscurrent1"];
$newpassword = $_POST["txtpassnew"];
$newpassword1 = $_POST["txtpassnew1"];

$sql = "";
$result = "";
if (($oldpassword == $oldpassword1) && ($newpassword == $newpassword1)) {
    $sql = "update spk_tlogin set password='$newpassword' where id_login = $idlogin";
    $result = @sybase_query($sql, $connection);
}

echo $sql;
echo $result;

if ($result) {
    header('Location: ../tukar-katalaluan?action=update-password-success');
} else {
    header('Location: ../tukar-katalaluan?action=update-password-fail');
}
?>