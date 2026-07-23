<?php
include("../../action/config.php");
include("../../action/senarai-kod.php");

//search
if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi"]) || isset($_POST["selectProgram"])) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION["pengajianpeo"] = $_POST["selectPengajian"];
    $_SESSION["sesipeo"] = $_POST["selectSesi"];
    $_SESSION["programpeo"] = $_POST["selectProgram"];
    header('Location: index.php');
    exit();
}
$_SESSION["pengajianpeo"] = isset($_SESSION["pengajianpeo"]) ? $_SESSION["pengajianpeo"] : '';
$_SESSION["sesipeo"] = isset($_SESSION["sesipeo"]) ? $_SESSION["sesipeo"] : '';
$_SESSION["programpeo"] = isset($_SESSION["programpeo"]) ? $_SESSION["programpeo"] : '';

$kodTerm = "";
if ($_SESSION["pengajianpeo"] == "Asasi")
    $kodTerm = "f005term like 'B%'";
else if ($_SESSION["pengajianpeo"] == "Diploma")
    $kodTerm = "f005term like 'E%'";
else if ($_SESSION["pengajianpeo"] == "Sarjana Muda")
    $kodTerm = "f005term like 'A%'";


//Retrieve Sesi Kemasukan
$sql = "select distinct(sesi2), LEFT( f005term, LEN( f005term ) -1 ) as term from v005_spk where $kodTerm order by sesi2 desc";
$sql_result_termList = @sybase_query($sql, $connection);

//Retrieve Tahap Pengajian
$sql = "select * from v006_spk where tahap_pengajian='" . $_SESSION["pengajianpeo"] . "' AND fakulti_singkatan = '" . $_SESSION['ptj'] . "' order by program";
$sql_result_programList = @sybase_query($sql, $connection);

//Selected Sesi
$sql = "select * from v005_spk where sesi2='".$_SESSION["sesipeo"]."' AND $kodTerm";
$sql_result_term = @sybase_query($sql, $connection);
$row = @sybase_fetch_array($sql_result_term);

//Selected Program
$sql = "select * from v006_spk where id_program='".$_SESSION["programpeo"]."'";
$sql_result_program = @sybase_query($sql, $connection);
$row_program = @sybase_fetch_array($sql_result_program);

//Retrieve PEO List
$sql = "select * from spk_tpeo where status_aktif=1 and sesi='".$_SESSION["sesipeo"]."' and kod_program='".$_SESSION["programpeo"]."'";
$sql_result_peo_list = @sybase_query($sql, $connection);




?>
