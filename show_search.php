<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}

$in_str = $_GET['id'];
if (!$in_str) {
    header("location: show_list.php");
    exit;
}

$page_name = "Search: ";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $app_name; ?> - <?php echo $page_name; ?></title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png"/>

</head>
<body>
<div class="container-scroller">

    <?php require_once 'partials/_navbar.php'; ?>
    <div class="container-fluid page-body-wrapper">
        <?php require_once 'partials/_sidebar.php'; ?>
        <div class="main-panel">
            <div class="content-wrapper">

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <h2><?php echo $page_name; ?></h2>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-end flex-wrap">

                                <a href="show_add.php" class="btn btn-primary mt-2 mt-xl-0"><i
                                            class="mdi mdi-plus-circle-outline btn-icon-prepend"></i> Add Shows</a>
                            </div>

                        </div>

                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatables" class="datatable" data-order='[[ 2, "desc" ]]'
                                           data-page-length='100' data-state-save="true" style="width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Show Name</th>
                                            <th>Year</th>
                                            <th>Runtime</th>
                                            <th>Votes</th>
                                            <th>Genres</th>
                                            <th>Description</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <?php
                                        $results = $mysqli->show_list();
                                        foreach ($results as $result) {
                                            ?>
                                            <tr>
                                                <td><?php echo $result['id'] ?></td>
                                                <td>
                                                    <a href="show_page.php?id=<?php echo $result['id'] ?>"> <?php echo $result['show_name'] ?></a>
                                                </td>
                                                <td><?php echo $result['year']; ?></td>
                                                <td><?php echo $result['runtime']; ?></td>
                                                <td><?php echo $result['votes']; ?></td>
                                                <td><?php echo $result['genres']; ?></td>
                                                <td><?php echo $result['description']; ?></td>
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

                </div>


            </div>
            <!-- content-wrapper ends -->
            <?php require_once 'partials/_footer.php'; ?>
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

<!-- plugins:js -->
<script src="vendors/base/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page-->
<script src="vendors/chart.js/Chart.min.js"></script>
<script src="vendors/datatables.net/jquery.dataTables.js"></script>
<script src="vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
<!-- End plugin js for this page-->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<!-- endinject -->
<!-- Custom js for this page-->

<script src="js/data-table.js"></script>
<script src="js/jquery.dataTables.js"></script>
<script src="js/dataTables.bootstrap4.js"></script>

<script>
    $(document).ready(function () {
        $('.datatable').DataTable();
    });
</script>


<!-- End custom js for this page-->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>

</html>
