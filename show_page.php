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

$img_url = 'https://image.tmdb.org/t/p/original';

$temp_url = $img_url . $show_info['show_poster_path'];

$back_url = $img_url . $show_info['show_backdrop_path'];

$year = substr($show_info['show_air_date'], 0, 4);

$page_name = $show_info['show_name'];

$reviews = $mysqli->show_reviews($show_info['id'], -1);

$review_count = count($reviews);

$genres = $mysqli->show_genres($in_id);

$r_str = '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $page_name; ?></title>
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
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div style="float:left">
                                    <img src="<?php echo $temp_url; ?>" width="333" height="500"
                                         style="margin-right:50px" alt="Image could not be loaded."/>
                                </div>
                                <div class="flex-wrap justify-content-md-between">
                                    <h2 class="flex-wrap"
                                        style="padding-bottom:10px"><?php echo $page_name; ?></h2>
                                    <h4 class="flex-wrap" style="padding-bottom:10px"><?php echo $year; ?></h4>
                                    <p class="flex-wrap"
                                       style="padding-bottom:10px"><?php echo $show_info['show_overview']; ?></p>
                                    <p class="flex-wrap"><?php echo $review_count ?> reviews</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-end flex-lg-column"
                                     style="float:right">
                                    <a href="show_review.php?id=<?php echo $in_id ?>"
                                       id="review_button" class="btn btn-primary mt-2 mt-xl-0">Write a
                                        review</a>
                                    <script>
                                        // removes review button if user is guest
                                        var check = parseInt('<?php echo $_SESSION[PREFIX . '_security']?>');
                                        if (check < 5) {
                                            document.getElementById('review_button').remove();
                                        }
                                    </script>
                                </div>
                                    <h5 class="flex-wrap">Genres: </h5>
                                    <?php
                                        if(empty($genres)){
                                            ?>
                                            <p>None</p>
                                            <?php
                                        }
                                        else{
                                            foreach($genres as $genre){
                                                $g_name = $genre['genre_name'];
                                                switch($genre['genre_name']){
                                                    case "Action & Adventure":
                                                        $g_name = "Action";
                                                        break;
                                                    case "Sci-Fi & Fantasy":
                                                        $g_name = "SciFi";
                                                        break;
                                                }
                                                ?>
                                                <p style="flex-direction: row">
                                                    <b>
                                                        <a class="one" style="color: #282f3a; flex-direction: row" href="genre_page.php?genre=<?php echo $g_name;?>"><?php echo $genre['genre_name'];?></a>
                                                    </b>
                                                </p>
                                            <?php
                                            }
                                        }
                                    ?>

                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="dashboard-tabs p-0">
                                <ul class="nav nav-tabs px-4">
                                    <li class="nav-item">
                                        <a id="rec-review-tab" class="nav-link active">Recent reviews</a>
                                    </li>
                                    <li class="nav-item">
                                        <a id="all-review-tab" class="nav-link"
                                           href="all_reviews.php?id=<?php echo $in_id; ?>">All reviews</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                if (empty($reviews)) {
                                    ?>
                                    <div class="col-sm-12 grid-margin stretch-card" style="border: none; outline: none">
                                        <div class="card" style="border: none; outline: none">
                                            <div class="card-body" style="border: none; outline: none">
                                                <div class="card-title">No reviews yet!</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else {
                                    $max = 5;
                                    // make sure to not go loop past the amount of reviews
                                    if ($review_count < $max) {
                                        $max = $review_count;
                                    }
                                    $i = 0;
                                    while ($i < $max) {
                                        switch ($reviews[$i]["review_value"]) {
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
                                        } ?>
                                        <p>
                                            <b><a class="one" style="color: #282f3a"
                                                  href="review_page.php?rid=<?php echo $reviews[$i]["review_id"] ?>&sid=<?php echo $reviews[$i]["show_id"] ?>&uid=<?php echo $reviews[$i]["user_id"] ?>"><?php
                                                    $user_info = $mysqli->user_info($reviews[$i]['user_id']);
                                                    $user_pf = $mysqli->user_pf_info($reviews[$i]['user_id']);
                                                    $pfp = $user_pf['profile_pic_src'];
                                                    ?>
                                                    <img src="images/faces/<?php echo $pfp ?>"
                                                         onerror="this.onerror=null; this.src='images/faces/dummy_pfp.jpg';"
                                                         style="width: 50px; height: 50px; border-radius: 100%;"
                                                    />
                                                    <span><?php echo $user_info['user_name'] . "'" . "s review " ?></span>
                                                    <span style="color: #0072ff"><?php echo $r_str; ?></span></a></b>
                                        </p>
                                        <p><?php echo $reviews[$i]['review_content']; ?></p>
                                        <p style="padding-bottom:10px; border-bottom: 2px solid grey;"></p>
                                        <?php
                                        $i++;
                                    }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content wrapper ends -->
            <?php require_once 'partials/_footer.php'; ?>
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

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>
</html>


