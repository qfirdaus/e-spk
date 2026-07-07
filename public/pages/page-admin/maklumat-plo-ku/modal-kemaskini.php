
<div id="kemaskini" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $lang['TTL-KEMASKINI-PLO'] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form autocomplete="off" action="sql_update_plo.php" method="POST">
                <div class="modal-body"><div class="row">
                        <div class="col-lg-12 portlets">
                            <section class="panel">

                                <div class="panel-body">
                                    <span class="arrow"></span>
                                    <div class="col-xs-12 col-sm-12 col-md-12 co2l-lg-12 col-xl-12">						
                                        <input name="txtidplo" id="txtidplo" type="hidden" class="form-control" autocomplete="off" readonly="">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-SESI-KEMASUKAN']?></label>
                                            <div class="col-sm-5">
                                                <input name="txtsesi" id="txtsesi" type="text" class="form-control" autocomplete="off" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-PROGRAM']?></label>
                                            <div class="col-sm-10">
                                                <input name="txtprogram" id="txtprogram" type="text" class="form-control" autocomplete="off" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-KOD-PLO']?></label>
                                            <div class="col-sm-5">
                                                <input name="txtkodplo" id="txtkodplo" type="text" class="form-control" placeholder="Kod PLO" autocomplete="off" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-KETERANGAN-PLO']?></label>
                                            <div class="col-sm-10">
                                                <textarea name="txtketeranganplo" id="txtketeranganplo" class="form-control" autocomplete="off"> </textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-KOD-MQF']?></label>
                                            <div class="col-sm-5">
                                                <select class="form-control" name="selectkodmqf" id="selectkodmqf">
                                                    <option value="">- <?= $lang['SELECT-PILIH']?> -</option>
                                                    <?php
                                                    while ($result = @sybase_fetch_array($sql_result_mqf_list2)) {
                                                    ?>
                                                        <option value="<?= $result["kod_mqf"] ?>"><?= $result["kod_mqf"] ?></option>
                                                    <?php 
                                                    $i++;
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <!--<div class="form-group row">
                                            <fieldset class="form-control" style="margin: 15px">
                                                <legend class="col-sm-2 col-form-label f-14" style="font-weight: bold"><?= $lang['TTL-SENARAI-PEO'] ?></legend>
                                                <div class="checkbox-zoom zoom-default" style="padding: 20px">
                                                    <?php while ($result = @sybase_fetch_array($sql_result_peo_list2)) { ?>
                                                        <div class="form-group row">
                                                            <label>
                                                                <input type="checkbox" name="chkpeo[]" value="<?= $result["id_peo"] ?>">
                                                                <span class="cr">
                                                                    <i class="cr-icon ik ik-check txt-default"></i>
                                                                </span>
                                                                <span><?= $result["kod_peo"] ?> : <?= $result["keterangan_bm"] ?></span>
                                                            </label>
                                                        </div>
                                                    <?php } ?>
                                                </div>

                                            </fieldset>
                                        </div>-->
                                    </div>                                                                                             
                                </div>                          
                            </section>                  
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="btnUpdateAcademicRoleIn" onclick="return confirm('Do you wish to save the information?')" class="btn btn-primary"><?= $lang['BTN-SIMPAN']?></button> 
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $lang['BTN-BATAL']?></button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).on("click", "#btnUpdateKaunselor", function () {

        var idkaunselor = $(this).data('idkaunselor');
        $(".modal-body #txtidkaunselor").val(idkaunselor);

        var nama = $(this).data('nama');
        $(".modal-body #txtnama").val(nama);

        var idstaf = $(this).data('idstaf');
        $(".modal-body #txtnostaf").val(idstaf);

        var jawatan = $(this).data('jawatan');
        $(".modal-body #txtjawatan").val(jawatan);

        var nolesen = $(this).data('nolesen');
        $(".modal-body #txtnolesen").val(nolesen);

        var nokaunselor = $(this).data('nokaunselor');
        $(".modal-body #txtnokaunselor").val(nokaunselor);

        var email = $(this).data('email');
        $(".modal-body #txtemel").val(email);

        var notel = $(this).data('notel');
        $(".modal-body #txtnotel").val(notel);



    });

</script>