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
                                            <span><?= $lang['TTL-MAKLUMAT-MQF'] ?></span>
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
                                <div class="card" >
                                    <div class="card-header d-block">
                                        <h3><?= $lang['PANEL-MQF'] ?></h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="list-actions" style="float: right; margin-bottom:10px;">
                                                    <button class="button-round" type="button" name="btnTambah" id="btnTambah" data-toggle="modal" data-target="#tambah" 
                                                            title="<?= $lang['TTP-TAMBAH-MQF'] ?>"><i class="ik ik-plus text-primary"></i></button>
                                                </div>
                                                <div class="card">
                                                    <div class="card-body">

                                                        <div class="dt-responsive">

                                                            <table id="order-table"
                                                                   class="table table-bordered nowrap table-hover" >
                                                                <thead>
                                                                    <tr>
                                                                        <th><?= $lang['COL-BIL'] ?></th>
                                                                        <th><?= $lang['COL-MQF'] ?></th>
                                                                        <th><?= $lang['COL-TARIKH-KEMASKINI'] ?></th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $i = 1;
                                                                    while ($result = @sybase_fetch_array($sql_result)) {
                                                                        $tarikhkemaskini = date("d-M-Y", strtotime($result["created_date"]));
                                                                        if ($result["updated_date"] != NULL)
                                                                            $tarikhkemaskini = date("d-M-Y", strtotime($result["updated_date"]));
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= $i ?></td>
                                                                            <td><?= $result["kod_mqf"] ?></td>
                                                                            <td><?= $tarikhkemaskini ?></td>
                                                                            <td width="100px">
                                                                    <center>
                                                                        <div class="list-actions">
                                                                            <form id="deleteForm<?= $i ?>" action="sql_delete_mqf.php" method="POST">
                                                                                <input type="hidden" name="txtidmqf" value="<?= $result["id_mqf"]; ?>"> 
                                                                            </form>

                                                                            <button class="button-round" type="button" name="btnKemaskini" id="btnKemaskini" data-toggle="modal" data-target="#kemaskini" 
                                                                                    data-idkemahiran="<?= $result['id_mqf'] ?>"   
                                                                                    data-kemahiran="<?= $result['kod_mqf'] ?>"  
                                                                                    title="<?= $lang['TTP-KEMASKINI'] ?>"><i class="ik ik-edit text-green"></i></button>
                                                                            <button type="submit" class="button-round" onclick="deleteFunc(<?= $i ?>)" title="<?= $lang['TTP-HAPUS'] ?>"><i class="ik ik-trash-2 text-red"></i></button>
                                                                        </div>
                                                                    </center>
                                                                    </td>

                                                                    </tr>
                                                                    <?php
                                                                    $i++;
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
                                                                            function deleteFunc(id) {
                                                                                if (confirm('Do you wish to delete the information?'))
                                                                                    document.getElementById('deleteForm' + id).submit();
                                                                                else
                                                                                    return false;
                                                                            }

                                                                            $(document).on("click", "#btnKemaskini", function () {
                                                                                var idkemahiran = $(this).data('idkemahiran');
                                                                                $("#kemaskini .modal-body #txtidkemahiran").val(idkemahiran);

                                                                                var kemahiran = $(this).data('kemahiran');
                                                                                $("#kemaskini .modal-body #txtkemahiran").val(kemahiran);

                                                                            });




        </script>
    </body>
</html>
