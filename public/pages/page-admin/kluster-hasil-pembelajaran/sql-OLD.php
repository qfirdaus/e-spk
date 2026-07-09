<?php
include("../../action/config.php");

//Retrieve LOC
$sql = "select * from spk_tloc where status_aktif=1 order by loc";
$sql_result = @sybase_query($sql, $connection);


?>
