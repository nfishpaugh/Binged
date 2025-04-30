<?php
require "include/config.inc";
require "include/utils.php";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("location: login.php");
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

$reviews = $mysqli->show_reviews($show_info['id'], 5);

$avg = $mysqli->get_show_column($show_info['id'], "review_avg");
if (!isset($avg)) {
    $avg = 0.0;
}

$review_count = $mysqli->get_show_column($show_info['id'], "review_amt");

$genres = $mysqli->show_genres($in_id);

$r_str = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION[PREFIX . '_user_id'])) {
    foreach ($_POST as $key => $val) {
        if (preg_match("/(delete|edit)[0-9]./", $key)) {
            // retrieves the command (delete or edit)
            $cmd = substr($key, 0, strcspn($key, "0123456789"));

            // creates a substring starting at the first numeric character of $val
            $ids = substr($key, strcspn($key, "0123456789"));

            // get indices of hyphen characters to use as starting points for ids
            $first = strpos($ids, "-");
            $second = strposX($ids, "-", 2);

            // arrid = id in array
            // rid = review id
            // uid = author's user id
            $arrid = substr($ids, 0, $first);
            $rid = substr($ids, $first + 1, $second - 2);
            $uid = substr($ids, $second + 1);

            $sid = $show_info['id'];

            if ($_SESSION[PREFIX . '_user_id'] == $uid && $cmd === "delete") {
                $mysqli->review_delete($rid);
                update_avg($sid, $reviews[$arrid]["review_value"], $review_count, $mysqli, true);

                header("location: show_page.php?id=" . $in_id);
            } else {
                $_SESSION["edit_review_content"] = $reviews[$arrid]["review_content"];
                $_SESSION["edit_review_value"] = $reviews[$arrid]["review_value"];
                $_SESSION["edit_review_date"] = $reviews[$arrid]["review_date"];
                $_SESSION["edit_show_id"] = $in_id;
                header("location: review_edit.php?rid=" . $rid . "&uid=" . $uid);
            }
            exit;
        }
    }
    //$mysqli->review_delete($reviews);
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && (!isset($_SESSION[PREFIX . '_user_id']))) {
    ?>
    <script>
        alert("Invalid user id");
    </script><?php
}
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
    <link rel="shortcut icon" href="images/binged_logo.svg"/>
</head>
<body>
<div class="container-scroller">

    <?php require_once 'partials/_navbar.php'; ?>
    <div class="container-fluid page-body-wrapper">
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
                                    <h4 class="flex-wrap" style="padding-bottom: 10px">Average
                                        score: <?php echo $avg; ?></h4>
                                    <p class="flex-wrap"
                                       style="padding-bottom:10px"><?php echo $show_info['show_overview']; ?></p>
                                </div>
                                <div class="d-flex justify-content-between align-items-end flex-lg-column"
                                     style="float:right">
                                    <a href="show_review.php?id=<?php echo $in_id ?>"
                                       id="review_button" class="btn btn-primary mt-2 mt-xl-0">Write a
                                        review</a>
                                    <script>
                                        // removes review button if user is guest
                                        let check = parseInt('<?php echo $_SESSION[PREFIX . '_security']?>');
                                        if (check < 5) {
                                            document.getElementById('review_button').remove();
                                        }
                                    </script>
                                </div>
                                <h5 class="flex-wrap">Genres: </h5>
                                <?php
                                if (empty($genres)) {
                                    ?>
                                    <p>None</p>
                                    <?php
                                } else {
                                    foreach ($genres as $genre) {
                                        $g_name = match ($genre['genre_name']) {
                                            "Action & Adventure" => "Action",
                                            "Sci-Fi & Fantasy" => "SciFi",
                                            "War & Politics" => "War",
                                            default => $genre['genre_name']
                                        };
                                        ?>
                                        <p style="flex-direction: row">
                                            <b>
                                                <a class="one" style="color: #282f3a; flex-direction: row"
                                                   href="genre_page.php?genre=<?php echo $g_name; ?>"><?php echo $genre['genre_name']; ?></a>
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
                                    <li class="nav-item active">
                                        <a id="rec-review-tab" class="nav-link active">Recent reviews</a>
                                    </li>
                                    <!-- TODO - Refactor into different tabs on the same page, not two separate pages -->
                                    <!-- TODO - Pagination for the All Reviews tab -->
                                    <li class="nav-item">
                                        <a id="all-review-tab" class="nav-link"
                                           href="all_reviews.php?id=<?php echo $in_id; ?>">All reviews
                                            (<?php echo $review_count ?>)</a>
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
                                    // make sure to not go loop past the limit
                                    if ($review_count < $max) {
                                        $max = $review_count;
                                    }
                                    $i = 0;
                                    while ($i < $max) {
                                        $rev_id = $reviews[$i]["review_id"];
                                        $show_id = $reviews[$i]["show_id"];
                                        $user_id = $reviews[$i]["user_id"];
                                        $r_str = starify($reviews[$i]["review_value"]); ?>
                                        <p>
                                            <b><a class="one" style="color: #282f3a"
                                                  href="review_page.php?rid=<?php echo $rev_id ?>&sid=<?php echo $show_id ?>&uid=<?php echo $user_id ?>"><?php
                                                    $user_info = $mysqli->user_info($user_id);
                                                    $user_pf = $mysqli->user_pf_info($user_id);
                                                    $pfp = $user_pf['profile_pic_src'] ?? 'dummy_pfp.jpg';
                                                    ?>
                                                    <img src="images/faces/<?php echo $pfp ?>"
                                                         onerror="this.onerror=null; this.src='images/faces/dummy_pfp.jpg';"
                                                         style="width: 50px; height: 50px; border-radius: 100%;"
                                                    />
                                                    <span><?php echo $user_info['user_name'] . "'s review" ?></span>
                                                    <span style="color: #0072ff"><?php echo $r_str; ?></span></a></b>
                                        </p>
                                        <p><?php echo $reviews[$i]['review_content']; ?></p>
                                        <?php if (isset($user_info['user_id']) && $user_info['user_id'] == $_SESSION[PREFIX . '_user_id']) { ?>
                                            <form action="" method="POST">
                                                <button class="d-inline btn btn-secondary"
                                                        name="edit<?php echo $i; ?>-<?php echo $reviews[$i]["review_id"]; ?>-<?php echo $user_info['user_id']; ?>">
                                                    Edit
                                                </button>
                                                <button class="d-inline btn btn-primary"
                                                        name="delete<?php echo $i; ?>-<?php echo $reviews[$i]["review_id"]; ?>-<?php echo $user_info['user_id']; ?>">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php } ?>
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
<!-- End plugin js for this page-->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<!-- endinject -->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>
</html>


