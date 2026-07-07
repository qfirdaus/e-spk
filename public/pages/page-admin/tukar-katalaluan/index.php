<?php
include("../../includes/session.php");
include("sql.php");
?>
<!doctype html>
<html class="no-js" lang="en">
    <!-- HEAD -->
    <?php include("../../includes/head.php"); ?>

    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <div class="wrapper">

            <!-- HEADER -->
            <?php include("../../includes/header.php"); ?>

            <div class="page-wrap">

                <!-- SIDEBAR -->
                <?php include("../../includes/sidebar.php"); ?>

                <div class="main-content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"><h3><?= $lang['PANEL-TUKAR-KATALALUAN'] ?></h3></div>
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-2" > <center>
                                                    <div id="visitfromworld" style="width:100%; ">
                                                        <img src="http://esmartcard.upnm.edu.my/img/staf/<?= $id_staf ?>.jpg" alt="" class="rounded-circle" height="130px">
                                                    </div></center>
                                            </div>
                                            <div class="col-md-10" style="border-left: 1px solid; background-color: lightgray; padding-top: 10px;">   
                                                <b><div class="row mb-15">
                                                        <div class="col-12"><?= $nama_staf ?> (<?= $no_staf ?>)</div>
                                                    </div>
                                                    <div class="row mb-15">
                                                        <div class="col-12"><?= $jabatan ?></div>
                                                    </div>
                                                    <div class="row mb-15">
                                                        <div class="col-12"><?= $role ?></div>
                                                    </div>
                                                </b>                                                
                                            </div>
                                        </div>
                                        <form action="sql-update.php" method="POST">
                                            <div class="row align-items-center">
                                                <div class="col-md-2" > <center>
                                                        <div id="visitfromworld" style="width:100%; ">

                                                        </div></center>
                                                </div>
                                                <div class="col-md-10" style="border-left: 1px solid">   

                                                    <input name="txtidlogin" type="hidden" value="<?= $id_login ?>">
                                                    <input name="txtpasscurrent" type="hidden" value="<?= $password ?>">
                                                    <div class="row mb-15">
                                                        <label class="col-sm-3 col-form-label"><?= $lang['LBL-NAMA-PENGGUNA'] ?></label>
                                                        <div class="col-sm-5">
                                                            <input name="txtusername" id="txtusername" type="text" class="form-control" autocomplete="off" readonly="" value="<?= $username ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-15">
                                                        <label class="col-sm-3 col-form-label"><?= $lang['LBL-KATALALUAN-SEKARANG'] ?></label>
                                                        <div class="col-sm-5">
                                                            <input name="txtpasscurrent1" id="txtpasscurrent1" type="password" class="form-control" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-15">
                                                        <label class="col-sm-3 col-form-label"><?= $lang['LBL-KATALALUAN-BAHARU'] ?></label>
                                                        <div class="col-sm-5">
                                                            <input name="txtpassnew" id="txtpassnew" type="password" class="form-control" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-15">
                                                        <label class="col-sm-3 col-form-label"><?= $lang['LBL-PENGESAHAN-KATALALUAN-BAHARU'] ?></label>
                                                        <div class="col-sm-5">
                                                            <input name="txtpassnew1" id="txtpassnew1" type="password" class="form-control" autocomplete="off">
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="btnKemaskini" onclick="return confirm('Do you wish to save the information?')" class="btn btn-primary"><?= $lang['BTN-SIMPAN'] ?></button> 
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <!-- FOOTER -->
            <?php include("../../includes/footer.php"); ?>


        </div>
    </div>



    <!-- SCRIPT -->
    <?php include("../../includes/script.php"); ?>
    <script src="../../js/tables.js"></script>


</body>
</html>
