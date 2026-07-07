
<div id="salin" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $lang['TTL-SALIN-PLO'] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form autocomplete="off" action="sql_copy_plo.php" method="POST">
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
                                                    <?php while ($result = @sybase_fetch_array($sql_result_termList1)) { ?>
                                                        <option value="<?= $result["f005term"] ?>"><?= $result["f005term"] ?> - <?= $result["semester"] ?></option>
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
                    <button type="submit" name="btnUpdateAcademicRoleIn" onclick="return confirm('Do you wish to save the information?')" class="btn btn-primary"><?= $lang['BTN-SALIN']?></button> 
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