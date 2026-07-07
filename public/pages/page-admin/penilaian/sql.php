<?php
include("../../action/config.php");

//Retrieve penilaian
$sql = "select * from spk_tpenilaian where status_aktif=1 order by penilaian";
$sql_result = @sybase_query($sql, $connection);


?>
