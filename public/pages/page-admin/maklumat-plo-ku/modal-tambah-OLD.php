
<div id="tambah" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $lang['TTL-TAMBAH-PLO'] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form autocomplete="off" action="sql_add_plo.php" method="POST">
                <div class="modal-body"><div class="row">
                        <div class="col-lg-12 portlets">
                            <section class="panel">
                                <div class="panel-body">
                                    <span class="arrow"></span>
                                    <div class="col-xs-12 col-sm-12 col-md-12 co2l-lg-12 col-xl-12">						
                                        <input name="txtprogramid" id="txtprogramid" type="hidden" class="form-control" autocomplete="off" readonly="">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-SESI-KEMASUKAN']?></label>
                                            <div class="col-sm-2">
                                                <input name="txtsesiid" id="txtsesiid" type="text" class="form-control" autocomplete="off" readonly="">
                                            </div>
                                            <div class="col-sm-8">
                                                <input name="txtsesi" id="txtsesi" type="text" class="form-control" autocomplete="off" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-KOD-PLO']?></label>
                                            <div class="col-sm-5">
                                                <select class="form-control" name="selectkodplo" id="selectkodplo">
                                                    <option value="">- <?= $lang['SELECT-PILIH']?> -</option>
                                                    <?php
                                                    $i=0;
                                                    while ($i < count($plo)) {
                                                    ?>
                                                        <option value="<?= $plo[$i] ?>"><?= $plo[$i] ?></option>
                                                    <?php 
                                                    $i++;
                                                    } ?>
                                                </select>
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
                                                    while ($result = @sybase_fetch_array($sql_result_mqf_list)) {
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
                                                <legend class="col-sm-2 col-form-label f-14" style="font-weight: bold"><?= $lang['PANEL-SENARAI-PEO'] ?></legend>
                                                <div class="checkbox-zoom zoom-default" style="padding: 20px">
                                                    <?php while ($result = @sybase_fetch_array($sql_result_peo_list)) { ?>
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
                    <button type="submit" name="btnTambah" onclick="return confirm('Do you wish to save the information?')" class="btn btn-primary"><?= $lang['BTN-SIMPAN']?></button> 
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $lang['BTN-BATAL']?></button>
                </div>
            </form>
        </div>
    </div>
</div>

