<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}

$in_id = (int)$_GET['id'];
if (!$in_id) {
    header("location: index.php");
    exit;
}
$show_info = $mysqli->show_info($in_id);

$img_url = $mysqli->tmdb_api($show_info['show_name']);

$page_name = $show_info['show_name'];

$reviews = $mysqli->show_reviews($show_info['id']);

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
                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div style="float:left">
                                    <img src="<?php echo $img_url ?>" width="333" height="500"
                                         style="margin-right:50px" alt="Image could not be loaded."/>
                                </div>
                                <div class="flex-wrap">
                                    <h2 class="flex-wrap"
                                        style="padding-bottom:20px"><?php echo $page_name . " (" . $show_info['year'] . ")" ?></h2>
                                    <p class="flex-wrap"
                                       style="padding-bottom:10px"><?php echo $show_info['description']; ?></p>
                                    <p class="flex-wrap">Runtime (min): <?php
                                        if (!is_null($show_info['runtime'])) {
                                            echo $show_info['runtime'];
                                        } else {
                                            echo "Not Available";
                                        } ?></p>
                                    <p class="flex-wrap">Likes: <?php echo $show_info['votes'] ?></p>
                                </div>
                                <div class="d-flex justify-content-between align-items-end flex-lg-column"
                                     style="float:right">
                                    <a href="show_review.php?id=<?php echo $in_id ?>"
                                       class="btn btn-primary mt-2 mt-xl-0"><i
                                                class="mdi mdi-plus-circle-outline btn-icon-prepend"></i>Write a
                                        review</a>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h2 style="border-bottom:2px solid grey; text-align:center; padding-bottom:10px">Recent
                                    Reviews</h2>
                                <?php
                                if(empty($reviews)){?>
                                    <div class="col-sm-12 grid-margin stretch-card" style="border: none; outline: none">
                                        <div class="card" style="border: none; outline: none">
                                            <div class="card-body" style="border: none; outline: none">
                                                <div class="card-title">No reviews yet!</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php }
                                foreach ($reviews as $review) { ?>
                                    <p>
                                        <b><a href="review_page.php?rid=<?php echo $review["review_id"] ?>&sid=<?php echo $review["show_id"] ?>&uid=<?php echo $review["user_id"] ?>"><?php
                                                $user_info = $mysqli->user_info($review['user_id']);
                                                echo $user_info['user_name'] . "'" . "s review:"; ?></a></b></p>
                                    <p><?php echo $review['review_content']; ?></p>
                                    <p>Rating: <?php
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
                                    <p style="padding-bottom:10px; border-bottom: 2px solid grey;">Review
                                        Date: <?php echo $review['review_date']; ?></p>
                                <?php } ?>
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


