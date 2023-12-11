<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}

$in_id = (int)$_GET['id'];
if (!$in_id) {
    header("location: user_list.php");
    exit;
}

$user_info = $mysqli->user_info($in_id);

$page_name = "" . $user_info['user_name'] . "'" . "s Reviews";

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

                                <a href="user_add.php" class="btn btn-primary mt-2 mt-xl-0"><i
                                            class="mdi mdi-plus-circle-outline btn-icon-prepend"></i> Add User</a>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="row no-gutters">
                    <?php
                    $results = $mysqli->user_review_info($in_id);
                    if (empty($results)) { ?>
                        <div class="col-sm-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title">No reviews found
                                        for <?php echo $user_info['user_name'] ?></div>
                                </div>
                            </div>
                        </div>
                    <?php } else {
                        foreach ($results as $result) {
                            $img_url = $mysqli->tmdb_api($result['show_name']); ?>
                            <div class="col-sm-3 grid-margin stretch-card" style="border-radius: 15px">
                                <div class="card flex-row flex-wrap" style="border-radius: 15px">
                                    <div class="card-header border-0" style="back">
                                        <a href="review_page.php?rid=<?php echo $result['review_id']; ?>&sid=<?php echo $result['id']; ?>&uid=<?php echo $in_id; ?>"><img
                                                    src="<?php echo $img_url ?>" class="card-img"
                                                    style="max-width: 30%; max-height: 100%; object-fit: scale-down"
                                                    alt=""/></a>
                                    </div>
                                    <div class="card-description" style="padding:5px; border-radius: 15px">
                                        <a href="review_page.php?rid=<?php echo $result['review_id']; ?>&sid=<?php echo $result['id']; ?>&uid=<?php echo $in_id; ?>"
                                           style="text-decoration: none; color: inherit">
                                            <p class="card-title"><?php echo $result['show_name']; ?></p>
                                            <p class="card-text" style=""><?php echo $result['review_content']; ?></p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
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


