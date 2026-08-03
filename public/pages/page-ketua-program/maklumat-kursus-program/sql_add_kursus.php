<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}

$sesiid = $_POST["txtsesiid"];
$programid = $_POST["txtprogramid"];
$kursus = $_POST["selectkursus"];
$kategorikursus = $_POST["selectKategoriKursus"];
$programuniversiti = 'Program';

//$kodmqf = $_POST["selectkodmqf"];
//$keteranganbm = $_POST["txtketeranganplo"];
$created_by = $_SESSION['id_staf'];
$sql = "insert into spk_tkursus(program_universiti, kod_kursus, term_pengajian, kategori_kursus, kod_program, kod_jabatan, created_by, created_date) values ('$programuniversiti', '$kursus', '$sesiid', '$kategorikursus', '$programid', '" . $_SESSION['ptj'] . "', '$created_by', GETDATE())";
$result_add = @sybase_query($sql, $connection); 

if ($result_add) {
    header('Location: ../maklumat-kursus-program?action=save-success');
} else {
    header('Location: ../maklumat-kursus-program?action=save-fail');
}
?>