<?php
include("../../action/config.php");
?>
<style rel="stylesheet" type="text/css">

    th {
        height: 50px;
        font-weight: bold;
        text-align: center;
        background-color: #cccccc;
    }



</style>
<?php
// Check connection
if ($connection === false) {
    die("ERROR: Could not connect. " . sybase_connect_error());
}

if (isset($_REQUEST["term"])) {

    $var = $_REQUEST["term"];
    $sql = "Select TOP 5 * from stafdb.dbo.v630staf_service_skim_aktif staf join stafdb.dbo.v_senarai_FPJB fpjb on staf.kdjbtnhakiki = fpjb.kod_jabatan where LOWER(gelar_nama) like LOWER('%$var%') OR LOWER(nopekerja) like LOWER('$var%')";
    $sql_result_search = @sybase_query($sql, $connection);


    if (sybase_num_rows($sql_result_search) > 0) {

        echo "<table class='table table-bordered'><thead><tr><th>No.Staf</th><th>Nama</th> <th style='width:10'></th></tr></thead>";
        while ($row = @sybase_fetch_array($sql_result_search)) {
            echo "<tr><td align='center'> " . $row["nopekerja"] . " </td>"
            . "<td  align='left'> " . strtoupper($row["gelar_nama"]) . " </td> "
            . "<td><center><button class='button-round' type='button' name='btnTambah' id='btnTambah' data-toggle='modal' data-target='#tambah' 
                                                                                    data-nostaf='" . $row["nopekerja"] . "' 
                                                                                    data-nokp='" . $row["nokp"] . "' 
                                                                                    data-nama='" . $row["gelar_nama"] . "' 
                                                                                    data-jabatansingkatan='" . $row["nama_singkat_jabatan"] . "' 
                                                                                    data-jabatan='" . $row["jabatanhakiki"] . "' 
                                                                                    data-notel='" . $row["telefon_pej"] . "' 
                                                                                    data-emel='" . $row["email"] . "' 
                                                                                    title='Tambah Pengguna'><i class='ik ik-plus text-primary'></i></button></center></td></tr>";
        }
        echo "</table>";
    } else
        echo "<p><center>Tiada rekod ditemui.</center</p>";
}

// close connection
sybase_close($connection);

?>
