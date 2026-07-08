<?php

include("../../action/config.php");
if (!isset($_SESSION)) {
    session_start();
}


$updated_by = $_SESSION['id_staf'];
$result = null;

//reset penyelaras
if (isset($_GET["btnReset"])=='penyelaras') {
    $idkursus = $_GET["idKursus"];
    $sql = "update spk_tkursus set penyelaras_kursus = NULL, updated_by='$updated_by', updated_date=GETDATE() where id_kursus=$idkursus";
    $result = @sybase_query($sql, $connection);
    if ($result) {
        header('Location: ../maklumat-kursus-universiti?action=save-success');
    } else {
        header('Location: ../maklumat-kursus-universiti?action=save-fail');
    }
}


//update kategori kursus
if (isset($_POST["selectKategoriKursus"])) {
    $kod_kursus = $_POST["txtKodKursus"];
    $kategori_kursus = $_POST["selectKategoriKursus"];
    if ($kategori_kursus != "0") {
        $sql = "update spk_tkursus set kod_program = '".$_SESSION["programkursus"]."', kategori_kursus = '$kategori_kursus', updated_by='$updated_by', updated_date=GETDATE() where id_kursus=$id_kursus";
        $result = @sybase_query($sql, $connection);
    }
    if ($result) {
        header('Location: ../maklumat-kursus-universiti?action=save-success');
    } else {
        header('Location: ../maklumat-kursus-universiti?action=save-fail');
    }
}

//update penyelaras
if (isset($_POST["selectPenyelaras"])) {
    $id_kursus = $_POST["txtIdKursus"];
    $coordinator = $_POST["selectPenyelaras"];
    if ($coordinator != "0") {
        $sql = "update spk_tkursus set penyelaras_kursus = '$coordinator', updated_by='$updated_by', updated_date=GETDATE() where id_kursus=$id_kursus";
        $result = @sybase_query($sql, $connection);

        //retrieve coordinator info
        $sql_penyelaras = "select gelar_nama, nokp from stafdb.dbo.v630staf_service_skim_aktif "
                . "where nopekerja = '$coordinator' ";
        $sql_result_penyelaras = @sybase_query($sql_penyelaras, $connection);
        $row = @sybase_fetch_array($sql_result_penyelaras);
        $coodinator_name = $row["gelar_nama"];
        $coodinator_ic = '123456'; //$row["nokp"];
        $idrole = 3;
        $created_by = $_SESSION['id_staf'];

        //insert into spk_tlogin
        /*$sql = "insert into spk_tlogin(id_staf, nama, username, password, id_role, jabatan, created_by, created_date) "
        . "SELECT '$coordinator','$coodinator_name','$coordinator', '$coodinator_ic', $idrole, '" . $_SESSION['kod_ptj'] . "', '$created_by', GETDATE() WHERE NOT EXISTS 
        (select id_staf from spk_tlogin WHERE id_staf='$coordinator')";
        $result = @sybase_query($sql, $connection);*/
		
		$sql = "select id_staf from spk_tlogin where id_staf='$coordinator'";
		$sql_result = @sybase_query($sql, $connection);
		$count = sybase_num_rows($sql_result);

		if ($count == 0) {
			$sql = "insert into spk_tlogin(id_staf, nama, username, password, jabatan, created_by, created_date) values ('$coordinator','$coodinator_name','$coordinator', '$coodinator_ic', '" . $_SESSION['kod_ptj'] . "', '$created_by', GETDATE())";
			$result = @sybase_query($sql, $connection);
		}

		$sql = "select id_staf from spk_tpenetapan_login_role where id_staf='$coordinator' and id_role=$idrole";
		$sql_result = @sybase_query($sql, $connection);
		$count = sybase_num_rows($sql_result);

		if ($count == 0) {
			$sql = "insert into spk_tpenetapan_login_role (id_staf, id_role, created_by, created_date) "
                . "values ('$coordinator', $idrole, '$created_by', GETDATE())"; 
			$sql_result = @sybase_query($sql, $connection);
		}
		
    }
    if ($result) {
        header('Location: ../maklumat-kursus-universiti?action=save-success');
    } else {
        header('Location: ../maklumat-kursus-universiti?action=save-fail');
    }
}

?>