<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}

$page_name = "Most Popular";

$img_url = 'https://image.tmdb.org/t/p/original';
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
        <?php //require_once 'partials/_sidebar.php'; ?>
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


                <div class="row no-gutters">
                    <?php
                    $results = $mysqli->show_list();
                    foreach ($results as $result) {
                        $temp_url = $img_url . $result['show_poster_path']; ?>
                        <div class="col-4 col-md-3 col-lg-2 grid-margin stretch-card" style="border-radius: 0">
                            <div class="card flex-wrap" style="border-radius: 0">
                                <div class="container-lg">
                                    <div class="card-img">
                                        <a href="show_page.php?id=<?php echo $result['id']; ?>/"><img
                                                    src="<?php echo $temp_url ?>" class="card-img"
                                                    style="max-width: 100%; max-height: 100%; object-fit: scale-down"
                                                    alt=""/></a>
                                    </div>
                                </div>


                                <div class="card-description">
                                    <a href="show_page.php?id=<?php echo $result['id']; ?>"
                                       style="text-decoration: none; color: inherit">
                                        <p class="card-title" style="flex-wrap: wrap; white-space: normal; overflow: visible; height: 5vh"><?php echo $result['show_name'] ?></p>
                                        <!-- <p class="card-text" style=""><?php //echo $result['show_overview']; ?></p> -->
                                    </a>
                                </div>

                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <a class="one" href="genre_page.php?genre=<?php echo "Action" ?>">
                                        <h2>Action & Adventure</h2></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters">
                    <?php
                    $results = $mysqli->show_list_genre("Action & Adventure", 18);
                    foreach ($results as $result) {
                        $temp_url = $img_url . $result['show_poster_path'];
                        //$year = substr($result['show_air_date'], 0, 4); ?>
                        <div class="col-4 col-md-3 col-lg-2 grid-margin stretch-card" style="border-radius: 0">
                            <div class="card flex-wrap" style="border-radius: 0; outline: 0;">
                                    <div class="container-lg">
                                        <div class="card-img" style="overflow: hidden; object-fit: fill">
                                            <a href="show_page.php?id=<?php echo $result['id']; ?>"><img
                                                        src="<?php echo $temp_url ?>" class="card-img"
                                                        alt=""/></a>
                                        </div>
                                    </div>

                                    <div class="card-description">
                                        <a href="show_page.php?id=<?php echo $result['id']; ?>"
                                           style="text-decoration: none; color: inherit">
                                            <p class="card-title" style="flex-wrap: wrap; white-space: normal; overflow: visible; height: 5vh"><?php echo $result['show_name'] ?></p>
                                            <!-- <p class="card-text" style=""><?php //echo $result['show_overview']; ?></p> -->
                                        </a>
                                    </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <a class="one" href="genre_page.php?genre=<?php echo "Drama" ?>">
                                        <h2>Drama</h2></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters">
                    <?php
                    $results = $mysqli->show_list_genre("Drama", 18);
                    foreach ($results as $result) {
                        $temp_url = $img_url . $result['show_poster_path'];
                        //$year = substr($result['show_air_date'], 0, 4); ?>
                        <div class="col-4 col-md-3 col-lg-2 grid-margin stretch-card" style="border-radius: 0">
                            <div class="card flex-wrap" style="border-radius: 0">
                                <div class="container-lg">
                                    <div class="card-img">
                                        <a href="show_page.php?id=<?php echo $result['id']; ?>"><img
                                                    src="<?php echo $temp_url ?>" class="card-img"
                                                    style="max-width: 100%; max-height: 100%; object-fit: scale-down"
                                                    alt=""/></a>
                                    </div>
                                </div>

                                <div class="card-description">
                                    <a href="show_page.php?id=<?php echo $result['id']; ?>"
                                       style="text-decoration: none; color: inherit">
                                        <p class="card-title" style="flex-wrap: wrap; white-space: normal; overflow: visible; height: 5vh"><?php echo $result['show_name'] ?></p>
                                        <!-- <p class="card-text" style=""><?php //echo $result['show_overview']; ?></p> -->
                                    </a>
                                </div>

                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <a class="one" href="genre_page.php?genre=<?php echo "Comedy" ?>">
                                        <h2>Comedy</h2></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters">
                    <?php
                    $results = $mysqli->show_list_genre("Comedy", 18);
                    foreach ($results as $result) {
                        $temp_url = $img_url . $result['show_poster_path'];
                        //$year = substr($result['show_air_date'], 0, 4); ?>
                        <div class="col-4 col-md-3 col-lg-2 grid-margin stretch-card" style="border-radius: 0">
                            <div class="card flex-wrap" style="border-radius: 0">
                                <div class="container-lg">
                                    <div class="card-img">
                                        <a href="show_page.php?id=<?php echo $result['id']; ?>"><img
                                                    src="<?php echo $temp_url ?>" class="card-img"
                                                    style="max-width: 100%; max-height: 100%; object-fit: scale-down"
                                                    alt=""/></a>
                                    </div>
                                </div>

                                <div class="card-description">
                                    <a href="show_page.php?id=<?php echo $result['id']; ?>"
                                       style="text-decoration: none; color: inherit">
                                        <p class="card-title" style="flex-wrap: wrap; white-space: normal; overflow: visible; height: 5vh"><?php echo $result['show_name'] ?></p>
                                        <!-- <p class="card-text" style=""><?php //echo $result['show_overview']; ?></p> -->
                                    </a>
                                </div>

                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <a class="one" href="genre_page.php?genre=<?php echo "SciFi" ?>">
                                        <h2>Sci-Fi & Fantasy</h2></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters">
                    <?php
                    $results = $mysqli->show_list_genre("Sci-Fi & Fantasy", 18);
                    foreach ($results as $result) {
                        $temp_url = $img_url . $result['show_poster_path'];
                        //$year = substr($result['show_air_date'], 0, 4); ?>
                        <div class="col-4 col-md-3 col-lg-2 grid-margin stretch-card" style="border-radius: 0">
                            <div class="card flex-wrap" style="border-radius: 0">
                                <div class="container-lg">
                                    <div class="card-img">
                                        <a href="show_page.php?id=<?php echo $result['id']; ?>"><img
                                                    src="<?php echo $temp_url ?>" class="card-img"
                                                    style="max-width: 100%; max-height: 100%; object-fit: scale-down"
                                                    alt=""/></a>
                                    </div>
                                </div>

                                <div class="card-description">
                                    <a href="show_page.php?id=<?php echo $result['id']; ?>"
                                       style="text-decoration: none; color: inherit">
                                        <p class="card-title" style="flex-wrap: wrap; white-space: normal; overflow: visible; height: 5vh"><?php echo $result['show_name'] ?></p>
                                        <!-- <p class="card-text" style=""><?php //echo $result['show_overview']; ?></p> -->
                                    </a>
                                </div>

                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

            </div>
            <!-- content-wrapper ends -->
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
<!-- End custom js for this page-->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>

</html>

