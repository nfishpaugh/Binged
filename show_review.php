<?php

include "include/config.inc";
include "include/utils.php";

if ($_SESSION[PREFIX . '_security'] < 5) {
    $_POST = array();
    header("location: " . $_SESSION[PREFIX . "_ppage"]);
    exit;
}

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    $_POST = array();
    header("Location: login.php");
    exit;
}

$in_id = (int)$_GET['id'];
if (!$in_id) {
    header("location: index.php");
    exit;
}

$show_info = $mysqli->show_info($in_id);

$page_name = $show_info['show_name'];

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['review_sub'])) {

    $rating = match ($_POST['rate']) {
        "5" => 5,
        "4" => 4,
        "3" => 3,
        "2" => 2,
        default => 1,
    };

    if (!$review_count = $mysqli->get_show_column($in_id, "review_amt")) {
        $review_count = 0;
    }
    update_avg($in_id, $rating, $review_count, $mysqli);

    $mysqli->review_insert($rating, $_POST['review_content'], $in_id, $_SESSION[PREFIX . '_user_id']);

    $mysqli->actions_insert("Added Review: " . date("Y-m-d") . " SID: " . $in_id, $_SESSION[PREFIX . '_user_id']);

    $_SESSION[PREFIX . '_action'][] = 'added';
    header("location: show_page.php?id=" . $in_id);
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['cancel'])) {
    header("location: show_page.php?id=" . $in_id);
    exit;
}//END POST

$img_url = 'https://image.tmdb.org/t/p/original/' . $show_info['show_poster_path'];

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
    <link rel="shortcut icon" href="images/binged_logo.svg"/>
</head>
<body>
<div class="container-scroller">

    <?php require_once 'partials/_navbar.php'; ?>
    <div class="container-fluid page-body-wrapper">
        <div class="main-panel">
            <div class="content-wrapper">

                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="me-md-3 me-xl-5">
                                    <div class="container-md-1" style="float:left; max-width:20vw;">
                                        <div class="card-img-2">
                                            <a href="show_page.php?id=<?php echo $in_id ?>">
                                                <img src="<?php echo $img_url ?>" width="333" height="500"
                                                     alt="Image could not be loaded."/>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex-wrap" style="float:left">
                                        <h2><?php echo $page_name; ?></h2>
                                        <form id="form-rev" name="form-rev" action="" method="post">
                                            <textarea id="review_content" name="review_content" rows="5"
                                                      cols="50"
                                                      style="border: 2px solid black; border-radius: 5px; padding: 20px"
                                                      placeholder="Enter your review for <?php echo $page_name ?>..."></textarea>
                                            <br>
                                            <div class="rate flex-wrap" style="margin-left: -0.5vw">
                                                <?php for ($i = 5; $i >= 1; $i--) { ?>
                                                    <input type="radio" id="star<?php echo $i; ?>" name="rate"
                                                           value="<?php echo $i; ?>"/>
                                                    <label for="star<?php echo $i; ?>" title="text"><?php echo $i; ?>
                                                        stars</label>
                                                <?php } ?>
                                            </div>
                                            <br>
                                            <button type="submit" class="btn btn-primary mt-2 mt-xl-0" name="review_sub"
                                                    style="float:right">
                                                <i class="mdi mdi-plus-circle-outline btn-icon-prepend"></i>
                                                Submit Review
                                            </button>
                                            <button type="submit" class="btn btn-primary mt-2 mt-xl-0" name="cancel">
                                                <i class="mdi mdi-minus-circle-outline btn-icon-prepend"></i>
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- content wrapper ends -->
            </div>
        </div>
    </div>
    <!-- plugins:js -->
    <script src="vendors/base/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page-->
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/hoverable-collapse.js"></script>
    <script src="js/template.js"></script>
    <!-- endinject -->

    <script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>
</html>
