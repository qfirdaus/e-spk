<?php
include("../../action/config.php");
include("../../action/senarai-kod.php");

if (!isset($_SESSION)) {
    session_start();
}

$_SESSION['ptj'] = 'FKJ';

//search
if (isset($_POST["selectPengajian"]) || isset($_POST["selectSesi"]) || isset($_POST["selectProgram"])) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION["pengajianplo"] = $_POST["selectPengajian"];
    $_SESSION["sesiplo"] = $_POST["selectSesi"];
    $_SESSION["programplo"] = $_POST["selectProgram"];
    header('Location: index.php');
    exit();
}
$_SESSION["pengajianplo"] = isset($_SESSION["pengajianplo"]) ? $_SESSION["pengajianplo"] : '';
$_SESSION["sesiplo"] = isset($_SESSION["sesiplo"]) ? $_SESSION["sesiplo"] : '';
$_SESSION["programplo"] = isset($_SESSION["programplo"]) ? $_SESSION["programplo"] : '';

$kodTerm = "";
if ($_SESSION["pengajianplo"] == "Asasi")
    $kodTerm = "f005term like 'B%'";
else if ($_SESSION["pengajianplo"] == "Diploma")
    $kodTerm = "f005term like 'E%'";
else if ($_SESSION["pengajianplo"] == "Sarjana Muda")
    $kodTerm = "f005term like 'A%'";


//Retrieve Sesi Semasa
$sql = "select * from v005_spk where $kodTerm order by f005term desc";
$sql_result_termList = @sybase_query($sql, $connection);
$sql_result_termList1 = @sybase_query($sql, $connection);

//Retrieve Tahap Pengajian
$sql = "select * from v006_spk where tahap_pengajian='" . $_SESSION["pengajianplo"] . "' AND fakulti_singkatan = '" . $_SESSION['ptj'] . "' order by program";
$sql_result_programList = @sybase_query($sql, $connection);
$sql_result_programList1 = @sybase_query($sql, $connection);

//Selected Sesi
$sql = "select * from v005_spk where f005term='" . $_SESSION["sesiplo"] . "'";
$sql_result_term = @sybase_query($sql, $connection);
$row_term = @sybase_fetch_array($sql_result_term);

//Selected Program
$sql = "select * from v006_spk where id_program='".$_SESSION["programplo"]."'";
$sql_result_program = @sybase_query($sql, $connection);
$row_program = @sybase_fetch_array($sql_result_program);



//Retrieve PEO List
$sql = "select * from spk_tpeo where status_aktif=1 and kod_sesi like '" . substr($_SESSION["sesiplo"], 0, -1) . "%' and kod_program='".$_SESSION["programplo"]."' order by kod_peo";
$sql_result_peo_list = @sybase_query($sql, $connection);
$sql_result_peo_list2 = @sybase_query($sql, $connection);
$total_peo = sybase_num_rows($sql_result_peo_list);

//Retrieve PLO List
$sql = "select * from spk_tplo where status_aktif=1 and kod_sesi='" .$_SESSION["sesiplo"]. "' and kod_program='".$_SESSION["programplo"]."' order by cast(SUBSTRING (kod_plo, 4, LEN(kod_plo)) as int) asc";
$sql_result_plo_list = @sybase_query($sql, $connection);
$total_plo = sybase_num_rows($sql_result_plo_list);

//Retrieve PLO List
$sql = "select * from v_spk_penetapan_peo_plo where kod_sesi='" .$_SESSION["sesiplo"]. "' and kod_program='".$_SESSION["programplo"]."'";
$sql_result_plo_peo_list = @sybase_query($sql, $connection);


$sql = "select a.kod_peo, count(*) as total, (count(a.id_peo) * 100.0 / SUM(count(a.id_peo)))  AS percentage  from spk_tpeo a join v_spk_penetapan_peo_plo b on a.id_peo=b.id_peo where a.status_aktif=1 and a.kod_sesi like '" . substr($_SESSION["sesiplo"], 0, -1) . "%' and a.kod_program='".$_SESSION["programplo"]."' group by a.kod_peo order by a.kod_peo";
$sql_result_program_peo_count = @sybase_query($sql, $connection);
$sql_result_program_peo_count2 = @sybase_query($sql, $connection);
$sql_result_program_peo_count_chart = @sybase_query($sql, $connection);

$sql = "SELECT  a.id_plo, a.kod_plo, count(b.id_plo) as total, (count(b.id_plo) * 100.0 / SUM(count(b.id_plo))) AS percentage FROM spk_tplo a left JOIN v_spk_penetapan_kursus_plo b ON a.id_plo = b.id_plo where a.kod_sesi='".$_SESSION["sesiplo"]."' and a.kod_program='".$_SESSION["programplo"]."' group by a.id_plo";
$sql_result_program_plo_count = @sybase_query($sql, $connection);
$sql_result_program_plo_count2 = @sybase_query($sql, $connection);
$sql_result_program_plo_count_chart = @sybase_query($sql, $connection);

?>