<?php
include("../../action/config.php");

//Retrieve kemahiran
$sql = "select * from spk_tmqf where status_aktif=1 order by kod_mqf";
$sql_result = @sybase_query($sql, $connection);


?>
