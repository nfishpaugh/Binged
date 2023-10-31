<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}
if ($_SESSION[PREFIX . '_security'] < 5) {
    header("location:index.php?action=5");
    exit;
}

$page_name = "Students";


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
                                    <p class="mb-md-0">text</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-end flex-wrap">

                                <a href="student_add.php" class="btn btn-primary mt-2 mt-xl-0"><i
                                            class="mdi mdi-plus-circle-outline btn-icon-prepend"></i> Add Student</a>
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
                                            <th>Student</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>DOB</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <?php
                                        $results = $mysqli->student_list();
                                        foreach ($results as $result) {
                                            ?>
                                            <tr>
                                                <td>
                                                    <a href="student_edit.php?id=<?php echo $result['student_id']; ?>"><?php echo $result['student_fname'] . " " . $result['student_lname']; ?></a>
                                                </td>
                                                <td><?php echo $result['student_email']; ?></td>
                                                <td><?php echo $result['student_phone']; ?></td>
                                                <td><?php echo $result['student_dob']; ?></td>
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

