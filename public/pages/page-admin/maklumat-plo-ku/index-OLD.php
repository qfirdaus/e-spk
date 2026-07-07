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
                                            <h5><?= $lang['TTL-PENETAPAN'] ?></h5>
                                            <span><?= $lang['TTL-MAKLUMAT-PLO'] ?></span>
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
                                    <div class="card-header"><h3><?= $lang['PANEL-CARIAN']?></h3></div>
                                    <div class="card-body">
                                        <form action="sql.php" method="POST">
                                            <div class="form-group col-sm-8 row">
                                                <label for="pengajian" class="col-sm-3 col-form-label"><?= $lang['LBL-PERINGKAT-PENGAJIAN']?></label>
                                                <div class="col-sm-8">                    
                                                    <select class="form-control" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH']?> -</option>
                                                        <option value="Asasi">ASASI</option>
                                                        <option value="Diploma">DIPLOMA</option>
                                                        <option value="Sarjana Muda">SARJANA MUDA</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-8 row">
                                                <label for="sesikemasukan" class="col-sm-3 col-form-label"><?= $lang['LBL-SESI-KEMASUKAN']?></label>
                                                <div class="col-sm-8">                    
                                                    <select class="form-control" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH']?> -</option>
                                                        <?php while ($result = @sybase_fetch_array($sql_result_termList)) { ?>
                                                            <option value="<?= $result["f005term"] ?>"><?= $result["f005term"] ?> - <?= $result["semester"] ?></option>
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
                                        <h3><?= $lang['PANEL-SENARAI-PLO']?> </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="list-actions" style="float: right; margin-bottom:10px;">
                                                    <button class="button-round" type="button" name="btnTambah" id="btnTambah" data-toggle="modal" data-target="#tambah" 
                                                            data-sesiid="<?= $row_term["f005term"] ?>"
                                                            data-sesi="<?= $row_term["semester"] ?>"
                                                            data-programid="<?= $row_program["id_program"] ?>"
                                                            data-program="<?= $row_program["program"] ?>"
                                                            title="<?= $lang['TTP-TAMBAH-PLO'] ?>"><i class="ik ik-plus text-primary"></i></button>
                                                    <button class="button-round" type="button" name="btnSalin" id="btnSalin" data-toggle="modal" data-target="#salin" 
                                                            data-sesi="<?= $row_term["f005term"] ?>"
                                                            data-programid="<?= $row_program["id_program"] ?>"
                                                            title="<?= $lang['TTP-SALIN-PLO'] ?>"><i class="ik ik-copy text-primary"></i></button>
                                                </div>
                                                <div class="card">
                                                    <div class="card-body">

                                                        <div class="dt-responsive">

                                                            <table id="order-table"
                                                                   class="table table-bordered wrap table-hover" >
                                                                <thead>
                                                                    <tr>
                                                                        <th><?= $lang['COL-KOD-PLO'] ?></th>
                                                                        <th><?= $lang['COL-KETERANGAN-PLO'] ?></th>
                                                                        <th><?= $lang['COL-MQF'] ?></th>
                                                                        <th><?= $lang['COL-SENARAI-PEO'] ?></th>
                                                                        <th><?= $lang['COL-SENARAI-CLO'] ?></th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    while ($result = @sybase_fetch_array($sql_result_plo_list)) {
                                                                        $strpeo = "";
                                                                        $sql_peo = "select kod_peo,keterangan_bm from spk_tpenetapan_peo_plo
                                                                                join spk_tpeo st on spk_tpenetapan_peo_plo.id_peo = st.id_peo
                                                                                where id_plo = " . $result["id_plo"];
                                                                        $sql_result_peo = @sybase_query($sql_peo, $connection);
                                                                        //echo $sql_peo;
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= $result["kod_plo"] ?></td>
                                                                            <td><?= $result["keterangan_bm"] ?></td>
                                                                            <td><?= $result["kod_mqf"] ?></td>
                                                                            <td> 
                                                                                <?php
                                                                                while ($result_peo = @sybase_fetch_array($sql_result_peo)) {
                                                                                    $strpeo = $strpeo . '<a class="table-a" title="' . $result_peo['keterangan_bm'] . '">' . $result_peo['kod_peo'] . '</a>, ';
                                                                                }

                                                                                $strpeo = substr($strpeo, 0, -3);
                                                                                echo $strpeo;
                                                                                ?>

                                                                            </td>
                                                                            <td></td>
                                                                            <td width="100px">
                                                                    <center>
                                                                        <div class="list-actions">
                                                                            <form id="deleteForm<?php echo 1 ?>" action="sql_delete_peo.php" method="POST">
                                                                                <input type="hidden" name="txtidpeo" value="<?= $result["id_peo"]; ?>"> 
                                                                            </form>

                                                                            <button class="button-round" type="button" name="btnKemaskini" id="btnKemaskini" data-toggle="modal" data-target="#kemaskini" 
                                                                                    data-sesiid="<?= $row_term["f005term"] ?>"
                                                                                    data-sesi="<?= $row_term["semester"] ?>"
                                                                                    data-programid="<?= $row_program["id_program"] ?>"
                                                                                    data-program="<?= $row_program["program"] ?>"
                                                                                    data-idplo="<?= $result["id_plo"] ?>"
                                                                                    data-kodplo="<?= $result["kod_plo"] ?>"
                                                                                    data-keteranganbm="<?= $result["keterangan_bm"] ?>"
                                                                                    data-peolist="[<?= $strpeo = ""; ?>]"
                                                                                    title="<?= $lang['TTP-KEMASKINI'] ?>"><i class="ik ik-edit text-green"></i></button>
                                                                            <button type="submit" class="button-round" onclick="deleteFunc(<?php echo 1 ?>)" title="<?= $lang['TTP-HAPUS'] ?>"><i class="ik ik-trash-2 text-red"></i></button>
                                                                        </div>
                                                                    </center>
                                                                    </td>

                                                                    </tr>
                                                                    <?php
                                                                }
                                                                ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>         
                                            <!-- end -->
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
                <!-- MODAL -->
                <?php
                include("modal-tambah.php");
                include("modal-kemaskini.php");
                include("modal-salin.php");
                ?>

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
                                                                                
                                                                                $(document).on("click", "#btnSalin", function () {
                                                                                    var sesi = $(this).data('sesi');
                                                                                    $("#salin .modal-body #txtsesi").val(sesi);
                                                                                    
                                                                                    var programid = $(this).data('programid');
                                                                                    $("#salin .modal-body #txtprogramid").val(programid);

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
                                                                                });


        </script>
    </body>
</html>
