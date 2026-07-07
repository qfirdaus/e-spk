<?php
    while ($result3 = sybase_fetch_array($sql_result3)) {
?>
    <table id="jenisKerosakan">
        <tr class = "JenisKerosakan<?php echo $i; ?>" data-id="<?php echo $result3["jenisKerosakan"]; ?>"></tr>
    </table>
    <table id="jumlahKerosakan">
        <tr class = "JenisKerosakan<?php echo $i; ?>" data-id="<?php echo $result3["jumlahKerosakan"]; ?>"></tr>
        
        
    </table>
    <?php
    $i++;
}

?>
<script>
    

</script>