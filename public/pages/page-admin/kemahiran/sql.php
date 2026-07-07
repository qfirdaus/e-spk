<?php
include("../../action/config.php");

//Retrieve kemahiran
$sql = "select * from spk_tkemahiran where status_aktif=1 order by kemahiran";
$sql_result = @sybase_query($sql, $connection);


?>
