
<div id="tambah" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kursus Baharu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form autocomplete="off" action="sql_add_kursus.php" method="POST">
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
                                            <label class="col-sm-2 col-form-label">Kursus</label>
                                            <div class="col-sm-5">
                                                <select class="form-control" name="selectkursus" id="selectkursus">
                                                    <option value="">- <?= $lang['SELECT-PILIH']?> -</option>
                                                    <?php
                                                    while ($result = @sybase_fetch_array($sql_result_subject_list1)) {
                                                    ?>
                                                        <option value="<?= $result["kodk"] ?>"><?= $result["kodk"] ?> - <?= $result["subjekbm"] ?></option>
                                                    <?php 
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Kategori Kursus</label>
                                            <div class="col-sm-5">
                                                <select class="form-control" name="selectKategoriKursus" id="selectKategoriKursus">
                                                      <option value="0"  >- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                      <option value="Teras">Teras</option>
                                                      <option value="Elektif">Elektif</option>
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
                    <button type="submit" name="btnTambah" onclick="return confirm('Do you wish to save the information?')" class="btn btn-primary"><?= $lang['BTN-SIMPAN']?></button> 
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $lang['BTN-BATAL']?></button>
                </div>
            </form>
        </div>
    </div>
</div>

