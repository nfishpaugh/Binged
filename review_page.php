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

$img_url = $mysqli->tmdb_api($show_info['show_name']);

$user_info = $mysqli->user_info($user_id);

$page_name = $user_info['user_name'] . "'" . "s review of " . $show_info['show_name'];

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
        <?php require_once 'partials/_sidebar.php'; ?>
        <div class="main-panel">
            <div class="content-wrapper">

                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div style="float:left">
                                    <a href="show_page.php?id=<?php echo $show_id ?>">
                                        <img src="<?php echo $img_url ?>" width="333" height="500"
                                             style="margin-right:50px" alt="Image could not be loaded."/>
                                    </a>
                                </div>
                                <div style="float:right padding-top:80px" class="flex-wrap">
                                    <h2 class="flex-wrap"
                                        style="padding-bottom: 20px"><?php echo $user_info['user_name'] . "'" . "s review of " . $show_info['show_name'] . " (" . $show_info['year'] . ")"; ?></h2>
                                    <p class="flex-wrap"
                                       style="padding-bottom: 10px"><?php echo $review['review_content']; ?></p>
                                    <p class="flex-wrap">Rating: <?php
                                        switch ($review["review_value"]) {
                                            case 0:
                                                echo "No Rating";
                                                break;
                                            case 1:
                                                echo "★";
                                                break;
                                            case 2:
                                                echo "★★";
                                                break;
                                            case 3:
                                                echo "★★★";
                                                break;
                                            case 4:
                                                echo "★★★★";
                                                break;
                                            case 5:
                                                echo "★★★★★";
                                                break;
                                            default:
                                                echo "None";
                                                break;
                                        }
                                        ?></p>
                                    <p class="flex-wrap">Review Date: <?php echo $review['review_date']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

