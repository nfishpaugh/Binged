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

$page_name = "Show Add";

if ($_SERVER['REQUEST_METHOD'] == "POST") {


    $mysqli->show_insert($_POST['show_name'], $_POST['year'], $_POST['runtime'], $_POST['votes'], $_POST['genres'], $_POST['description']);

    $mysqli->actions_insert("Added Show: " . $_POST['show_name'] . " " . $_POST['year'], $_SESSION[PREFIX . '_user_id']);


    $_SESSION[PREFIX . '_action'][] = 'added';
    header("location: index.php");
    exit;
}//END POST


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

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <form class="forms-sample" id="form1" action="" method="post">

                                    <div class="form-group row">
                                        <label for="show_name" class="col-sm-3 col-form-label">Show Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="show_name"
                                                   name="show_name" maxlength="128"
                                                   placeholder="Show Name" required autofocus>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="year" class="col-sm-3 col-form-label">Year</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="year"
                                                   name="year" max="2030" min="1950" placeholder="2000" required>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label for="runtime" class="col-sm-3 col-form-label">Runtime (minutes)</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="runtime"
                                                   name="runtime" max="10000" min="1" placeholder="10">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="votes" class="col-sm-3 col-form-label">Votes</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="votes"
                                                   name="votes" max="1000000" min="1" placeholder="1">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="genres" class="col-sm-3 col-form-label">Genres</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="genres"
                                                   name="genres" maxlength="256"
                                                   placeholder="Genres">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="description" class="col-sm-3 col-form-label">Description</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="description"
                                                   name="description" maxlength="500"
                                                   placeholder="Description">
                                        </div>
                                    </div>


                                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                                    <button class="btn btn-light">Cancel</button>
                                </form>
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
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<!-- endinject -->
<!-- Custom js for this page-->


<!-- End custom js for this page-->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>

</html>

