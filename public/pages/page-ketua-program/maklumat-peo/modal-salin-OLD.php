<?php
include("../../includes/session.php");
include("sql.php");
?>
<div id="salin" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $lang['TTL-SALIN-PEO']  ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form autocomplete="off" action="sql_copy_peo.php" method="POST">
                <div class="modal-body"><div class="row">
                        <div class="col-lg-12 portlets">
                            <section class="panel">

                                <div class="panel-body">
                                    <span class="arrow"></span>
                                    <div class="col-xs-12 col-sm-12 col-md-12 co2l-lg-12 col-xl-12">						
                                        <input name="txtsesi" id="txtsesi" type="hidden" class="form-control" autocomplete="off" readonly="">
                                        <input name="txtprogramid" id="txtprogramid" type="hidden" class="form-control" autocomplete="off" readonly="">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-SESI-KEMASUKAN']?></label>
                                            <div class="col-sm-7">
                                                <select class="form-control" name="selectSesiModal" id="selectSesi">
                                                    <option disabled="">- <?= $lang['SELECT-PILIH']?> -</option>
                                                    <?php while ($result = @sybase_fetch_array($sql_result_termList)) { ?>
                                                        <option value="<?= $result["sesi2"] ?>"><?= $result["term"] ?> - <?= $result["sesi2"] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label"><?= $lang['LBL-PROGRAM']?></label>
                                            <div class="col-sm-10">
                                                <select class="form-control" name="selectProgramModal" id="selectProgram">
                                                    <option value="" disabled="">- <?= $lang['SELECT-PILIH']?> -</option>
                                                    <?php while ($result = @sybase_fetch_array($sql_result_programList)) { ?>
                                                        <option value="<?= $result["id_program"] ?>"><?= $result["program"] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>                                                                                             
                                </div>                          
                            </section>                  
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="btnSalin" onclick="return confirm('Do you wish to save the information?')" class="btn btn-primary"><?= $lang['BTN-SALIN']?></button> 
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $lang['BTN-BATAL']?></button>
                </div>
            </form>
        </div>
    </div>
</div>

