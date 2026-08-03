<?php

include("../../action/config.php");

if (!isset($_SESSION)) {
    session_start();
}

$created_by = $_SESSION['id_staf'];

//Retrieve Sesi Semasa
//$sql = "select * from v005_spk where f005term like 'A%' order by f005term desc";
//$sql_result_termList = @sybase_query($sql, $connection);

//search

//search
if (isset($_POST["selectPengajian"])) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION["pengajiankursus"] = $_POST["selectPengajian"];
    header('Location: index.php');
    exit();
}
$_SESSION["pengajiankursus"] = isset($_SESSION["pengajiankursus"]) ? $_SESSION["pengajiankursus"] : '';

$kodTerm = "";
if ($_SESSION["pengajiankursus"] == "Asasi")
    $kodTerm = "f005term like 'B%'";
else if ($_SESSION["pengajiankursus"] == "Diploma")
    $kodTerm = "f005term like 'E%'";
else if ($_SESSION["pengajiankursus"] == "Sarjana Muda")
    $kodTerm = "f005term like 'A%'";


//Retrieve Sesi
$sql = "select distinct(sesi2), f005term, semester from v005_spk where $kodTerm order by sesi2 desc";
$sql_result_termList = @sybase_query($sql, $connection);


if (isset($_POST["selectSesi"])) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION["sesikursus"] = $_POST["selectSesi"];
    $sql = "select distinct(kodk), subjekbm, subjekbi from v270offer_spk where term='" . $_SESSION["sesikursus"] . "' and kdfakulti='" . $_SESSION['ptj'] . "' order by kodk";
    $sql_result_subject_list = @sybase_query($sql, $connection);
    while ($result = @sybase_fetch_array($sql_result_subject_list)) { 
        $sql = "select kod_kursus, term_pengajian from spk_tkursus where kod_kursus='" . $result["kodk"] . "' and term_pengajian='" . $_SESSION["sesikursus"] . "'";
        $result_check = @sybase_query($sql, $connection);
        $count = sybase_num_rows($result_check); 
 
        /*if ($count == 0) {
            $sql = "insert into spk_tkursus(kod_kursus, term_pengajian, kod_jabatan, created_by, created_date) values ('" . $result["kodk"] . "','" . $_SESSION['sesikursus'] . "','" . $_SESSION['ptj'] . "', '$created_by', GETDATE())";
            $result_add = @sybase_query($sql, $connection); 
            //echo $sql;
        }*/
    }
    exit(header('Location: index.php'));
    exit();
}

if (isset($_POST["selectSesi"])) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION["sesikursus"] = $_POST["selectSesi"];
    $sql = "select distinct(kodk), subjekbm, subjekbi from v270offer_spk where term='" . $_SESSION["sesikursus"] . "' and kdfakulti='" . $_SESSION['ptj'] . "' order by kodk";
    $sql_result_subject_list = @sybase_query($sql, $connection);
    while ($result = @sybase_fetch_array($sql_result_subject_list)) { 
        $sql = "select kod_kursus, term_pengajian from spk_tkursus where kod_kursus='" . $result["kodk"] . "' and term_pengajian='" . $_SESSION["sesikursus"] . "'";
        $result_check = @sybase_query($sql, $connection);
        $count = sybase_num_rows($result_check); 
 
        if ($count == 0) {
            $sql = "insert into spk_tkursus(kod_kursus, term_pengajian, kod_jabatan, created_by, created_date) values ('" . $result["kodk"] . "','" . $_SESSION['sesikursus'] . "','" . $_SESSION['ptj'] . "', '$created_by', GETDATE())";
            $result_add = @sybase_query($sql, $connection); 
            //echo $sql;
        }
    }
    exit(header('Location: index.php'));
    exit();
}
$_SESSION["sesikursus"] = isset($_SESSION["sesikursus"]) ? $_SESSION["sesikursus"] : '';


if (isset($_POST["selectProgram"])) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION["programkursus"] = $_POST["selectProgram"];
    exit(header('Location: index.php'));
    exit();
}

$_SESSION["programkursus"] = isset($_SESSION["programkursus"]) ? $_SESSION["programkursus"] : '';



//Retrieve Tahap Pengajian
$sql = "select * from v006_spk where tahap_pengajian='" . $_SESSION["pengajiankursus"] . "' AND fakulti_singkatan = '" . $_SESSION['ptj'] . "' order by program";
$sql_result_programList = @sybase_query($sql, $connection);
$sql_result_programList1 = @sybase_query($sql, $connection);

//Selected Sesi
$sql = "select * from v005_spk where f005term='" . $_SESSION["sesikursus"] . "'";
$sql_result_term = @sybase_query($sql, $connection);
$row_term = @sybase_fetch_array($sql_result_term);

//Retrieve Subject List
$sql = "select distinct (a.kod_kursus), a.id_kursus, a.term_pengajian, b.subjekbm, a.kategori_kursus, a.penyelaras_kursus from spk_tkursus a
join v270offer_spk  b on a.kod_kursus = b.kodk and a.term_pengajian = b.term
where  a.term_pengajian='" . $_SESSION["sesikursus"] . "' and a.kod_jabatan='" . $_SESSION['ptj'] . "' order by kod_kursus";
$sql_result_subject_list = @sybase_query($sql, $connection);
?>
