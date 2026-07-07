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
                                            <span><?= $lang['TTL-MAKLUMAT-KURSUS'] ?></span>
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
                                                <label for="pengajian" class="col-sm-2 col-form-label"><?= $lang['LBL-PERINGKAT-PENGAJIAN'] ?></label>
                                                <div class="col-sm-8">                    
                                                    <select class="form-control" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                        <option value="Asasi">ASASI</option>
                                                        <option value="Diploma">DIPLOMA</option>
                                                        <option value="Sarjana Muda">SARJANA MUDA</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                        <form action="sql.php" method="POST">
                                            <div class="form-group col-sm-8 row">
                                                <label for="sesikemasukan" class="col-sm-2 col-form-label"><?= $lang['LBL-SESI'] ?></label>
                                                <div class="col-sm-8">                    
                                                    <select class="form-control" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                                                        <option value="" disabled="">- <?= $lang['SELECT-PILIH'] ?> -</option>
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
                                        <h3><?= $lang['PANEL-SENARAI-KURSUS'] ?></h3>
                                    </div>

                                    <div class="card-body"> 
                                        <div class="row">
                                            <div class="col-md-12">
											<div class="list-actions" style="float: right; margin-bottom:10px;">
                                                    <button class="button-round" type="button" name="btnTambah" id="btnTambah" data-toggle="modal" data-target="#tambah" 
                                                            data-sesiid="<?= $row_term["f005term"] ?>"
                                                            data-sesi="<?= $row_term["semester"] ?>"
                                                           
                                                            title="<?= $lang['TTP-TAMBAH-PLO'] ?>"><i class="ik ik-plus text-primary"></i></button>
                                                </div>
                                                <!--
                                                <div class="list-actions" style="float: right; margin-bottom:10px;">
                                                    <form action="#" method="post">
                                                        <button type="submit" id="export_data" name='export_data' class="button-round" title="Eksport Senarai Kursus"><i class="ik ik-download text-primary"></i></button>
                                                    </form>
                                                </div>
                                                -->
                                                <div class="card">
                                                    <div class="card-body">

                                                        <div class="dt-responsive">

                                                            <table id="order-table"
                                                                   class="table table-hover table-bordered wrap">
                                                                <thead>
                                                                    <tr>

                                                                        <th><?= $lang['COL-KOD-KURSUS'] ?></th>
                                                                        <th><?= $lang['COL-NAMA-KURSUS'] ?></th>
                                                                        <th><?= $lang['COL-KATEGORI-KURSUS'] ?></th>
																		<th><?= $lang['COL-PENYELARAS'] ?></th>
                                                                        <th hidden=""></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $i = 1;
                                                                    while ($result = @sybase_fetch_array($sql_result_subject_list)) {
                                                                        ?>
                                                                        <tr>

                                                                            <td><?= $result["kod_kursus"] ?></td>
                                                                            <td><?= $result["subjekbm"] ?></td>
                                                                            <td>
                                                                                <form action="sql_update_kursus.php" method="POST">
                                                                                    <input name="txtKodKursus" type="hidden" value="<?= $result["kod_kursus"] ?>"/>
                                                                                    <select class="form-control" name="selectKategoriKursus" id="selectKategoriKursus" onchange="this.form.submit()">
                                                                                        <?php if ($result["kategori_kursus"] != NULL) { ?>
                                                                                            <option value="<?= $result["kategori_kursus"] ?>"><?= $result["kategori_kursus"] ?></option>
                                                                                        <?php } else { ?>
                                                                                            <option value="0"  >- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                                                        <?php } ?>
                                                                                        <option value="Teras">Teras</option>
                                                                                        <option value="Elektif">Elektif</option>
                                                                                    </select>
                                                                                </form>
                                                                            </td>
																			<td> 
                                                                  
                                                                                <form action="sql_update_kursus.php" method="POST">
                                                                                    <div class="form-group col-sm-12 row">

                                                                                        <input name="txtIdKursus" type="hidden" value="<?= $result["id_kursus"] ?>"/>
                                                                                        <select class="form-control col-sm-10" name="selectPenyelaras" id="selectPenyelaras" onchange="this.form.submit()">
                                                                                            <?php
                                                                                            if ($result["penyelaras_kursus"] != NULL) {
                                                                                                $sql_pensyarah = "select nopekerja,gelar_nama from stafdb.dbo.v630staf_service_skim_aktif where nopekerja='" . $result["penyelaras_kursus"] . "'";
                                                                                                $sql_result_pensyarah = @sybase_query($sql_pensyarah, $connection);
                                                                                                $result_pensyarah = @sybase_fetch_array($sql_result_pensyarah)
                                                                                                ?>
                                                                                                <option value="<?= $result["penyelaras_kursus"] ?>"><?= $result_pensyarah["gelar_nama"] ?> - <?= $result_pensyarah["nopekerja"] ?></option>
                                                                                            <?php } else { ?>
                                                                                                <option value="0"  >- <?= $lang['SELECT-PILIH'] ?> -</option>
                                                                                            <?php } ?>
                                                                                            <?php
                                                                                            $sql_pensyarah = "select distinct (nopekerja), gelar_nama from v270offer_spk a left join stafdb.dbo.v630staf_service_skim_aktif s on a.stafno = CONVERT(varchar(10), s.idpekerja) where kodk='" . $result["kod_kursus"] . "' and term='" . $_SESSION["sesikursus"] . "'";
                                                                                            $sql_result_pensyarah = @sybase_query($sql_pensyarah, $connection);
                                                                                            while ($result_pensyarah = @sybase_fetch_array($sql_result_pensyarah)) {
                                                                                                ?>
                                                                                                <option value="<?= $result_pensyarah["nopekerja"] ?>"><?= $result_pensyarah["gelar_nama"] ?> - <?= $result_pensyarah["nopekerja"] ?></option>
                                                                                                <?php
                                                                                            }
                                                                                            ?>
                                                                                        </select>

                                                                                        <div class="col-sm-2">   
                                                                                            <button class="button-round" onclick="location.href = 'sql_update_kursus.php?btnReset=penyelaras&idKursus=<?= $result["id_kursus"] ?>';" type="button" name="btnReset" title="Reset"><i class="ik ik-repeat text-primary"></i></button>
                                                                                        </div>
                                                                                    </div>
                                                                                </form>
                                                                            </td>                                                                         
                                                                        </tr>

                                                                        </body>
                                                                        <?php
                                                                        $i++;
                                                                    }
                                                                    $i--;
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

                ?>
				
                <!-- FOOTER -->
                <?php include("../../includes/footer.php"); ?>
            </div>
        </div>




        <div class="modal fade apps-modal" id="appsModal" tabindex="-1" role="dialog" aria-labelledby="appsModalLabel" aria-hidden="true" data-backdrop="false">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="ik ik-x-circle"></i></button>
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="quick-search">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-4 ml-auto mr-auto">
                                    <div class="input-wrap">
                                        <input type="text" id="quick-search" class="form-control" placeholder="Search..." />
                                        <i class="ik ik-search"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body d-flex align-items-center">
                        <div class="container">
                            <div class="apps-wrap">
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-bar-chart-2"></i><span>Dashboard</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-mail"></i><span>Message</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-users"></i><span>Accounts</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-shopping-cart"></i><span>Sales</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-briefcase"></i><span>Purchase</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-server"></i><span>Menus</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-clipboard"></i><span>Pages</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-message-square"></i><span>Chats</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-map-pin"></i><span>Contacts</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-box"></i><span>Blocks</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-calendar"></i><span>Events</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-bell"></i><span>Notifications</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-pie-chart"></i><span>Reports</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-layers"></i><span>Tasks</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-edit"></i><span>Blogs</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-settings"></i><span>Settings</span></a>
                                </div>
                                <div class="app-item">
                                    <a href="#"><i class="ik ik-more-horizontal"></i><span>More</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                                                                        $(document).ready(function () {
                                                                                            document.getElementById('selectPengajian').value = '<?= $_SESSION["pengajiankursus"] ?>';
                                                                                            document.getElementById('selectSesi').value = '<?= $_SESSION["sesikursus"] ?>';
                                                                                           
                                                                                        });
																						
																				$(document).on("click", "#btnTambah", function () {
                                                                                    var sesiid = $(this).data('sesiid');
                                                                                    $("#tambah .modal-body #txtsesiid").val(sesiid);

                                                                                    var sesi = $(this).data('sesi');
                                                                                    $("#tambah .modal-body #txtsesi").val(sesi);

                                                                                });
        </script>
    </body>
</html>
