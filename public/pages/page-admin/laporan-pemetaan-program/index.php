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
                <div class="app-sidebar colored">
                    <div class="sidebar-header">
                        <a class="header-brand" href="index.html">
                            <div class="logo-img">
                                <img src="../src/img/brand-white.svg" class="header-brand-img" alt="lavalite"> 
                            </div>
                            <span class="text">ThemeKit</span>
                        </a>
                        <button type="button" class="nav-toggle"><i data-toggle="expanded" class="ik ik-toggle-right toggle-icon"></i></button>
                        <button id="sidebarClose" class="nav-close"><i class="ik ik-x"></i></button>
                    </div>

                    <!-- SIDEBAR -->
                    <?php include("../../includes/sidebar.php"); ?>
                </div>
                <div class="main-content">
                    <div class="container-fluid">
                        <div class="page-header">
                            <div class="row align-items-end">
                                <div class="col-lg-8">
                                    <div class="page-header-title">
                                        <i class="ik ik-settings bg-blue"></i>
                                        <div class="d-inline">
                                            <h5><?= $lang['TTL-LAPORAN'] ?></h5>
                                            <span><?= $lang['TTL-PEMETAAN-PROGRAM'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <!--<li class="breadcrumb-item">
                                                <a href="../index.html"><i class="ik ik-home"></i></a>
                                            </li>
                                            <li class="breadcrumb-item">
                                                <a href="#">Tables</a>
                                            </li>
                                            <li class="breadcrumb-item active" aria-current="page">Data Table</li>-->
                                        </ol>
                                    </nav>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header"><h3><?= $lang['PANEL-CARIAN'] ?></h3></div>
                                    <div class="card-body">
                                        <form action="sql.php" method="POST">
                                            <div class="form-group col-sm-8 row">
                                                <label for="pengajian" class="col-sm-3 col-form-label">Fakulti</label>
                                                <div class="col-sm-8">                    
                                                    <select class="form-control" onchange="this.form.submit()" name="selectFakulti" id="selectFakulti">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                        <option value="FKJ">FKJ</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-8 row">
                                                <label for="pengajian" class="col-sm-3 col-form-label"><?= $lang['LBL-PERINGKAT-PENGAJIAN'] ?></label>
                                                <div class="col-sm-8">                    
                                                    <select class="form-control" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                        <option value="Asasi">ASASI</option>
                                                        <option value="Diploma">DIPLOMA</option>
                                                        <option value="Sarjana Muda">SARJANA MUDA</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-8 row">
                                                <label for="sesikemasukan" class="col-sm-3 col-form-label"><?= $lang['LBL-SESI'] ?></label>
                                                <div class="col-sm-8">                    
                                                    <select class="form-control" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                        <?php while ($result = @sybase_fetch_array($sql_result_termList)) { ?>
                                                            <option value="<?= $result["f005term"] ?>"><?= $result["f005term"] ?> - <?= $result["semester"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group col-sm-8 row">
                                                <label for="program" class="col-sm-3 col-form-label"><?= $lang['LBL-PROGRAM'] ?></label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" onchange="this.form.submit()" name="selectProgram" id="selectProgram">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                        <?php while ($result = @sybase_fetch_array($sql_result_programList)) { ?>
                                                            <option value="<?= $result["id_program"] ?>"><?= $result["program"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <!-- Language - Comma Decimal Place table end -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card" >
                                    <div class="card-header d-block">
                                        <h3><?= $lang['PANEL-PEMETAAN-PLO-PEO'] ?> </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="dt-responsive">
                                                            <table id="order-table"
                                                                   class="table table-bordered wrap table-hover" >
                                                                <thead style="text-align: center">
                                                                    <tr>
                                                                        <th colspan="<?= $total_peo ?>"><?= $row_program['program'] ?></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <?php
                                                                        $i = 1;
                                                                        while ($result = @sybase_fetch_array($sql_result_peo_list)) {
                                                                            ?>
                                                                            <th><?= $result["kod_peo"] ?></th>
                                                                            <?php
                                                                            $i++;
                                                                        }
                                                                        $i--;
                                                                        ?>
                                                                    </tr>
                                                                </thead>                                                               
                                                                <tbody>
                                                                    <tr>
                                                                        <?php
                                                                        while ($result = @sybase_fetch_array($sql_result_program_peo_count)) {
                                                                            ?>
                                                                            <td style="text-align:center"><?= $result["total"] ?></td>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    </tr>
                                                                    <tr>
                                                                        <?php
                                                                        while ($result = @sybase_fetch_array($sql_result_program_peo_count2)) {
                                                                            ?>
                                                                            <td style="text-align:center"><?= round($result["percentage"], 1) ?>%
                                                                            <input type="text" class="peo" title="<?= $result["kod_peo"] ?>" value="<?= round($result["percentage"], 1)?>" hidden>
                                                                            </td>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>         
                                            <!-- end -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-xl-6">
                                            <div class="card">
                                                <div class="card-block text-center">
                                                    <div id="pie_peo"  class="chart-shadow" style="height:400px"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-6">
                                            <div class="card">
                                                <div class="card-block text-center">
                                                    <div id="bar_peo" class="chart-shadow" style="height:400px"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Language - Comma Decimal Place table end -->
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card" >
                                    <div class="card-header d-block">
                                        <h3><?= $lang['PANEL-PEMETAAN-CLO-PLO'] ?> </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="dt-responsive">
                                                            <table id="order-table"
                                                                   class="table table-bordered wrap table-hover" >
                                                                <thead style="text-align: center">
                                                                    <tr>
                                                                        <th colspan="<?= $total_plo ?>"><?= $row_program['program'] ?></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <?php
                                                                        $i = 1;
                                                                        while ($result = @sybase_fetch_array($sql_result_plo_list)) {
                                                                            ?>
                                                                            <th><?= $result["kod_plo"] ?></th>
                                                                            <?php
                                                                            $i++;
                                                                        }
                                                                        $i--;
                                                                        ?>
                                                                    </tr>

                                                                </thead>                                                               
                                                                <tbody><tr>
                                                                        <?php
                                                                        while ($result = @sybase_fetch_array($sql_result_program_plo_count)) {
                                                                            ?>
                                                                            <td style="text-align:center"><?= $result["total"] ?></td>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    </tr>
                                                                    <tr>
                                                                        <?php
                                                                        while ($result = @sybase_fetch_array($sql_result_program_plo_count2)) {
                                                                            ?>
                                                                            <td style="text-align:center"><?= round($result["percentage"], 1) ?>%
                                                                                <input type="text" class="plo" title="<?= $result["kod_plo"] ?>" value="<?= round($result["percentage"], 1)?>" hidden>
                                                                            </td>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>         
                                            <!-- end -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-xl-6">
                                            <div class="card">
                                                <div class="card-block text-center">
                                                    <div id="pie_plo"  class="chart-shadow" style="height:400px"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-6">
                                            <div class="card">
                                                <div class="card-block text-center">
                                                    <div id="bar_plo" class="chart-shadow" style="height:400px"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Language - Comma Decimal Place table end -->
                            </div>
                        </div>

                    </div>
                </div>
                <aside class="right-sidebar">
                    <div class="sidebar-chat" data-plugin="chat-sidebar">
                        <div class="sidebar-chat-info">
                            <h6>Chat List</h6>
                            <form class="mr-t-10">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Search for friends ..."> 
                                    <i class="ik ik-search"></i>
                                </div>
                            </form>
                        </div>
                        <div class="chat-list">
                            <div class="list-group row">
                                <a href="javascript:void(0)" class="list-group-item" data-chat-user="Gene Newman">
                                    <figure class="user--online">
                                        <img src="../img/users/1.jpg" class="rounded-circle" alt="">
                                    </figure><span><span class="name">Gene Newman</span>  <span class="username">@gene_newman</span> </span>
                                </a>
                                <a href="javascript:void(0)" class="list-group-item" data-chat-user="Billy Black">
                                    <figure class="user--online">
                                        <img src="../img/users/2.jpg" class="rounded-circle" alt="">
                                    </figure><span><span class="name">Billy Black</span>  <span class="username">@billyblack</span> </span>
                                </a>
                                <a href="javascript:void(0)" class="list-group-item" data-chat-user="Herbert Diaz">
                                    <figure class="user--online">
                                        <img src="../img/users/3.jpg" class="rounded-circle" alt="">
                                    </figure><span><span class="name">Herbert Diaz</span>  <span class="username">@herbert</span> </span>
                                </a>
                                <a href="javascript:void(0)" class="list-group-item" data-chat-user="Sylvia Harvey">
                                    <figure class="user--busy">
                                        <img src="../img/users/4.jpg" class="rounded-circle" alt="">
                                    </figure><span><span class="name">Sylvia Harvey</span>  <span class="username">@sylvia</span> </span>
                                </a>
                                <a href="javascript:void(0)" class="list-group-item active" data-chat-user="Marsha Hoffman">
                                    <figure class="user--busy">
                                        <img src="../img/users/5.jpg" class="rounded-circle" alt="">
                                    </figure><span><span class="name">Marsha Hoffman</span>  <span class="username">@m_hoffman</span> </span>
                                </a>
                                <a href="javascript:void(0)" class="list-group-item" data-chat-user="Mason Grant">
                                    <figure class="user--offline">
                                        <img src="../img/users/1.jpg" class="rounded-circle" alt="">
                                    </figure><span><span class="name">Mason Grant</span>  <span class="username">@masongrant</span> </span>
                                </a>
                                <a href="javascript:void(0)" class="list-group-item" data-chat-user="Shelly Sullivan">
                                    <figure class="user--offline">
                                        <img src="../img/users/2.jpg" class="rounded-circle" alt="">
                                    </figure><span><span class="name">Shelly Sullivan</span>  <span class="username">@shelly</span></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="chat-panel" hidden>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <a href="javascript:void(0);"><i class="ik ik-message-square text-success"></i></a>  
                            <span class="user-name">John Doe</span> 
                            <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
                        </div>
                        <div class="card-body">
                            <div class="widget-chat-activity flex-1">
                                <div class="messages">
                                    <div class="message media reply">
                                        <figure class="user--online">
                                            <a href="#">
                                                <img src="../img/users/3.jpg" class="rounded-circle" alt="">
                                            </a>
                                        </figure>
                                        <div class="message-body media-body">
                                            <p>Epic Cheeseburgers come in all kind of styles.</p>
                                        </div>
                                    </div>
                                    <div class="message media">
                                        <figure class="user--online">
                                            <a href="#">
                                                <img src="../img/users/1.jpg" class="rounded-circle" alt="">
                                            </a>
                                        </figure>
                                        <div class="message-body media-body">
                                            <p>Cheeseburgers make your knees weak.</p>
                                        </div>
                                    </div>
                                    <div class="message media reply">
                                        <figure class="user--offline">
                                            <a href="#">
                                                <img src="../img/users/5.jpg" class="rounded-circle" alt="">
                                            </a>
                                        </figure>
                                        <div class="message-body media-body">
                                            <p>Cheeseburgers will never let you down.</p>
                                            <p>They'll also never run around or desert you.</p>
                                        </div>
                                    </div>
                                    <div class="message media">
                                        <figure class="user--online">
                                            <a href="#">
                                                <img src="../img/users/1.jpg" class="rounded-circle" alt="">
                                            </a>
                                        </figure>
                                        <div class="message-body media-body">
                                            <p>A great cheeseburger is a gastronomical event.</p>
                                        </div>
                                    </div>
                                    <div class="message media reply">
                                        <figure class="user--busy">
                                            <a href="#">
                                                <img src="../img/users/5.jpg" class="rounded-circle" alt="">
                                            </a>
                                        </figure>
                                        <div class="message-body media-body">
                                            <p>There's a cheesy incarnation waiting for you no matter what you palete preferences are.</p>
                                        </div>
                                    </div>
                                    <div class="message media">
                                        <figure class="user--online">
                                            <a href="#">
                                                <img src="../img/users/1.jpg" class="rounded-circle" alt="">
                                            </a>
                                        </figure>
                                        <div class="message-body media-body">
                                            <p>If you are a vegan, we are sorry for you loss.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form action="javascript:void(0)" class="card-footer" method="post">
                            <div class="d-flex justify-content-end">
                                <textarea class="border-0 flex-1" rows="1" placeholder="Type your message here"></textarea>
                                <button class="btn btn-icon" type="submit"><i class="ik ik-arrow-right text-success"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- FOOTER -->
                <?php include("../../includes/footer.php"); ?>
            </div>
        </div>



        <!-- SCRIPT -->
        <?php include("../../includes/script.php"); ?>
        <script src="../../js/datatables.js"></script>
        <script src="../../plugins/sweetalert/dist/sweetalert.min.js"></script>
        <script src="../../plugins/summernote/dist/summernote-bs4.min.js"></script>
        <script src="../../dist/js/theme.min.js"></script>
        <script src="../../js/layouts.js"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script>window.jQuery || document.write('<script src="../src/js/vendor/jquery-3.3.1.min.js"><\/script>')</script>
        <script src="../../plugins/popper.js/dist/umd/popper.min.js"></script>
        <script src="../../plugins/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="../../plugins/perfect-scrollbar/dist/perfect-scrollbar.min.js"></script>
        <script src="../../plugins/screenfull/dist/screenfull.js"></script>
        <script src="../../plugins/amcharts/amcharts.js"></script>
        <script src="../../plugins/amcharts/gauge.js"></script>
        <script src="../../plugins/amcharts/serial.js"></script>
        <script src="../../plugins/amcharts/themes/light.js"></script>
        <script src="../../plugins/amcharts/animate.min.js"></script>
        <script src="../../plugins/amcharts/pie.js"></script>
        <script src="../../plugins/ammap3/ammap/ammap.js"></script>
        <script src="../../plugins/ammap3/ammap/maps/js/usaLow.js"></script>
        <script src="../../dist/js/theme.min.js"></script>
        <script src="chart-data.js"></script>

        <script>
                                                        $(document).on("click", "#btnTambah", function () {
                                                            var sesiid = $(this).data('sesiid');
                                                            $("#tambah .modal-body #txtsesiid").val(sesiid);

                                                            var sesi = $(this).data('sesi');
                                                            $("#tambah .modal-body #txtsesi").val(sesi);

                                                            var programid = $(this).data('programid');
                                                            $("#tambah .modal-body #txtprogramid").val(programid);

                                                            var program = $(this).data('program');
                                                            $("#tambah .modal-body #txtprogram").val(program);

                                                        });

                                                        $(document).on("click", "#btnKemaskini", function () {

                                                            var sesiid = $(this).data('sesiid');
                                                            $("#kemaskini .modal-body #txtsesiid").val(sesiid);

                                                            var sesi = $(this).data('sesi');
                                                            $("#kemaskini .modal-body #txtsesi").val(sesi);

                                                            var programid = $(this).data('programid');
                                                            $("#kemaskini .modal-body #txtprogramid").val(programid);

                                                            var program = $(this).data('program');
                                                            $("#kemaskini .modal-body #txtprogram").val(program);

                                                            var idplo = $(this).data('idplo');
                                                            $("#kemaskini .modal-body #txtidplo").val(idplo);

                                                            var kodplo = $(this).data('kodplo');
                                                            $("#kemaskini .modal-body #txtkodplo").val(kodplo);

                                                            var keteranganbm = $(this).data('keteranganbm');
                                                            $("#kemaskini .modal-body #txtketeranganplo").val(keteranganbm);


                                                        });


                                                        function deleteFunc(id) {
                                                            if (confirm('Do you wish to delete the information?'))
                                                                document.getElementById('deleteForm' + id).submit();
                                                            else
                                                                return false;
                                                        }
                                                        $(document).ready(function () {
                                                            document.getElementById('selectPengajian').value = '<?= $_SESSION["pengajianplo"] ?>';
                                                            document.getElementById('selectSesi').value = '<?= $_SESSION["sesiplo"] ?>';
                                                            document.getElementById('selectProgram').value = '<?= $_SESSION["programplo"] ?>';
                                                            document.getElementById('selectFakulti').value = '<?= $_SESSION["ptj"] ?>';
                                                        });


        </script>
    </body>
</html>
