
<div id="kemaskini" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $lang['TTL-KEMASKINI-MQF']  ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form autocomplete="off" action="sql_update_mqf.php" method="POST">
                <div class="modal-body"><div class="row">
                        <div class="col-lg-12 portlets">
                            <section class="panel">
                                <div class="panel-body">
                                    <span class="arrow"></span>
                                    <div class="col-xs-12 col-sm-12 col-md-12 co2l-lg-12 col-xl-12">		                                       
                                        <div class="form-group row">
                                            <input name="txtidmqf" id="txtidkemahiran" type="hidden" class="form-control" autocomplete="off">
                                            <label class="col-sm-3 col-form-label"><?= $lang['LBL-MQF']?></label>
                                            <div class="col-sm-5">
                                                <input name="txtmqf" id="txtkemahiran" type="text" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>                                                                                             
                                </div>                          
                            </section>                  
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="btnKemaskini" onclick="return confirm('Do you wish to save the information?')" class="btn btn-primary"><?= $lang['BTN-SIMPAN']?></button> 
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $lang['BTN-BATAL']?></button>
                </div>
            </form>
        </div>
    </div>
</div>
