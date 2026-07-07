<?php
include("../../action/config.php");

//Retrieve kaedah_pengajaran
$sql = "select * from spk_tkaedah_pengajaran where status_aktif=1 order by kaedah_pengajaran";
$sql_result = @sybase_query($sql, $connection);


?>
