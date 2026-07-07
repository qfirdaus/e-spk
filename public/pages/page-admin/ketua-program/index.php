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
                                            <span><?= $lang['TTL-MAKLUMAT-KETUA-PROGRAM'] ?></span>
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
                                    <div class="card-header"><h3>Carian</h3></div>
                                    <div class="card-body">
                                        <form class="app-search">
                                            <div class="form-group col-sm-9 row">
                                                <label for="staf" class="col-sm-1 col-form-label">Staf</label>
                                                <div class="col-sm-10 search-box">
                                                    <input type="text" class="form-control" id="txtstaf" placeholder="No.Staf / Nama">
                                                    <div class="result1" style="background-color: white"></div>
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
                                        <h3><?= $lang['PANEL-SENARAI-KETUA-PROGRAM'] ?></h3>
                                    </div>

                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-body">

                                                        <div class="dt-responsive">

                                                            <table id="order-table"
                                                                   class="table table-bordered nowrap table-hover" >
                                                                <thead>
                                                                    <tr>
                                                                        <th><?= $lang['COL-NO-STAF'] ?></th>
                                                                        <th><?= $lang['COL-NAMA-STAF'] ?></th>
                                                                        <th><?= $lang['COL-JABATAN'] ?></th>
                                                                        <th><?= $lang['COL-NO-TELEFON'] ?></th>
                                                                        <th><?= $lang['COL-EMEL'] ?></th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $i = 1;
                                                                    while ($result = @sybase_fetch_array($sql_result)) {
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= $result["id_staf"] ?></td>
                                                                            <td><?= $result["gelar_nama"] ?></td>
                                                                            <td><?= $result["nama_jabatan"] ?></td>
                                                                            <td><?= $result["telefon_pej"] ?></td>
                                                                            <td><?= $result["email"] ?></td>
                                                                            <td width="100px">
                                                                    <center>
                                                                        <div class="list-actions">
                                                                            <form id="deleteForm<?= $i ?>" action="sql_delete_user.php" method="POST">
                                                                                <input type="hidden" name="txtidstaf" value="<?= $result["id_staf"]; ?>"> 
                                                                            </form>
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
                                                                            
                                                                            $(document).ready(function () {
                                                                                    $('.search-box input[type="text"]').on("keyup input", function () {
                                                                                        /* Get input value on change */
                                                                                        var inputVal = $(this).val();
                                                                                        var resultDropdown = $(this).siblings(".result1");
                                                                                        if (inputVal.length) {
                                                                                            $.get("search.php?dir=<?php echo basename(dirname($_SERVER['SCRIPT_NAME'])) ?>", {term: inputVal}).done(function (data) {
                                                                                                // Display the returned data in browser
                                                                                                resultDropdown.html(data);
                                                                                            });
                                                                                        } else {
                                                                                            resultDropdown.empty();
                                                                                        }
                                                                                    });

                                                                                    // Set search input value on click of result item
                                                                                    $(document).on("click", ".result1 p", function () {
                                                                                        s
                                                                                        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
                                                                                        $(this).parent(".result1").empty();
                                                                                    });
                                                                                });
                                                                                
                                                                                $(document).on("click", "#btnTambah", function () { 
                                                                                    var nostaf = $(this).data('nostaf');
                                                                                    $("#tambah .modal-body #txtnostaf").val(nostaf);
                                                                                    
                                                                                    var nokp = $(this).data('nokp');
                                                                                    $("#tambah .modal-body #txtnokp").val(nokp);
                                                                                    
                                                                                    var nama = $(this).data('nama');
                                                                                    $("#tambah .modal-body #txtnama").val(nama);
                                                                                    
                                                                                    var jabatan = $(this).data('jabatan');
                                                                                    $("#tambah .modal-body #txtjabatan").val(jabatan);
                                                                                    
                                                                                    var jabatansingkatan = $(this).data('jabatansingkatan');
                                                                                    $("#tambah .modal-body #txtjabatansingkat").val(jabatansingkatan);
                                                                                    
                                                                                    var notel = $(this).data('notel');
                                                                                    $("#tambah .modal-body #txtnotel").val(notel);
                                                                                    
                                                                                    var emel = $(this).data('emel');
                                                                                    $("#tambah .modal-body #txtemel").val(emel);

                                                                                });

                                                                            $(document).on("click", "#btnKemaskini", function () {
                                                                                var idloc = $(this).data('idloc');
                                                                                $("#kemaskini .modal-body #txtidloc").val(idloc);

                                                                                var loc = $(this).data('loc');
                                                                                $("#kemaskini .modal-body #txtloc").val(loc);

                                                                            });




        </script>
    </body>
</html>
