<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}

$rev_id = (int)$_GET['rid'];
if (!$rev_id) {
    header("location: index.php");
    exit;
}

$show_id = (int)$_GET['sid'];
if (!$show_id) {
    header("location: index.php");
    exit;
}

$user_id = (int)$_GET['uid'];
if (!$user_id) {
    header("location: index.php");
    exit;
}

$review = $mysqli->review_info($rev_id);

$show_info = $mysqli->show_info($show_id);

$img_url = 'https://image.tmdb.org/t/p/original';

$user_info = $mysqli->user_info($user_id);

$user_pf = $mysqli->user_pf_info($review['user_id']);

if (isset($user_pf['profile_pic_src'])) {
    $pfp = $user_pf['profile_pic_src'];
} else {
    $pfp = 'dummy_pfp.jpg';
}

$page_name = $user_info['user_name'] . "'" . "s review of " . $show_info['show_name'];

$year = substr($show_info['show_air_date'], 0, 4);

$r_str = '';

switch ($review["review_value"]) {
    case 0:
        $r_str = "No Rating";
        break;
    case 1:
        $r_str = "★";
        break;
    case 2:
        $r_str = "★★";
        break;
    case 3:
        $r_str = "★★★";
        break;
    case 4:
        $r_str = "★★★★";
        break;
    case 5:
        $r_str = "★★★★★";
        break;
    default:
        $r_str = "None";
        break;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $app_name; ?> - <?php echo $page_name; ?></title>
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
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
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="container-md-1" style="float:left; max-width:20vw;">
                                    <div class="card-img-2">
                                        <a href="show_page.php?id=<?php echo $show_id ?>">
                                            <img src="<?php echo $img_url . $show_info['show_poster_path'] ?>"
                                                 width="333"
                                                 height="500"
                                                 style="margin-right:50px" alt="Image could not be loaded."/>
                                        </a>
                                    </div>
                                </div>
                                <div style="float:right padding-top:80px" class="flex-wrap">

                                    <a class="one" href="user_profile.php?id=<?php echo $user_id ?>">
                                        <h4 class="flex-wrap"
                                            style="padding-bottom: 10px;">
                                            <img src="images/faces/<?php echo $pfp ?>"
                                                 onerror="this.onerror=null; this.src='images/faces/dummy_pfp.jpg';"
                                                 style="width: 25px; height: 25px; border-radius: 100%;"/>
                                            <?php echo " Review by " . $user_info['user_name'] ?>
                                        </h4>
                                    </a>
                                    <a class="one" href="show_page.php?id=<?php echo $show_id ?>">
                                        <h2 class="flex-wrap"
                                            style="padding-bottom: 20px; line-height: 0.5;"><?php echo $show_info['show_name'] . " (" . $year . ")"; ?>
                                            <span class="flex-wrap"
                                                  style="padding-bottom: 20px; line-height: 0.5; color: #0072ff"><?php echo $r_str; ?></span>
                                        </h2>
                                    </a>
                                    <h5 class="flex-wrap"
                                        style=" color: darkgrey"><?php echo "Watched on " . $review['review_date']; ?></h5>
                                    <p class="flex-wrap"
                                       style="padding-bottom: 10px; padding-top: 10px;"><?php echo $review['review_content']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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

