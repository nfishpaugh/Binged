<?php
include "include/config.inc";
include "include/utils.php";

if ($_SESSION[PREFIX . '_username'] == "") {
    header("location: login.php");
    exit;
} elseif ($_SESSION[PREFIX . '_security'] < 5) {
    header("location: index.php");
    exit;
}

$rid = (int)$_GET['rid'];
$uid = (int)$_GET['uid'];

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['review_sub'])) {
    $rating = match ($_POST['rate']) {
        "5" => 5,
        "4" => 4,
        "3" => 3,
        "2" => 2,
        default => 1,
    };

    $show_id = $_SESSION[$rid . "_review_content"]['show_id'];

    $mysqli->review_update($rid, $rating, $_POST['review_content']);

    // only update the average if the rating changes
    if ($rating != $_SESSION[$rid . "_review_content"]['review_value']) {
        $review_count = $mysqli->get_show_column($show_id, "review_amt");

        // can just set the avg to the user's rating if it's the only review
        if (!$review_count || $review_count === 1) {
            $mysqli->update_show_column($show_id, $rating, "review_avg");
            $mysqli->update_show_column($show_id, 1, "review_amt");
        } else {
            update_avg($show_id, $rating, $review_count, $mysqli, false, true, $_SESSION[$rid . "_review_content"]['review_value']);
        }
    }

    $mysqli->actions_insert("Updated Review: " . date("Y-m-d") . " " . $show_id, $_SESSION[PREFIX . '_user_id']);

    $_SESSION[PREFIX . '_action'][] = 'updated';
    debug_to_console(array($rid, $uid, $_SESSION[$rid . "_review_content"]));
    unset($_SESSION[$rid . "_review_content"]);
    header("location: show_page.php?id=" . $show_id);
    exit;
}

if (!$rid || !$uid || $_SESSION[PREFIX . '_user_id'] !== $uid) {
    if (strpos($_SESSION[PREFIX . "_ppage"], "review_edit")) $_SESSION[PREFIX . "_ppage"] = "index.php";
    header("location: " . $_SESSION[PREFIX . "_ppage"]);
    exit;
}

if (!isset($_SESSION[$rid . "_review_content"])) {
    $_SESSION[$rid . "_review_content"] = $mysqli->review_info($rid);
}

if ($_SESSION[PREFIX . '_security'] == 1 || isset($_POST['cancel'])) {
    header("location: show_page.php?id=" . $_SESSION['edit_show_id']);
    exit;
}

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];

$poster = $mysqli->get_show_column($_SESSION[$rid . "_review_content"]['show_id'], "show_poster_path");
$poster = 'https://image.tmdb.org/t/p/original' . $poster;
$showname = $mysqli->get_show_column($_SESSION[$rid . "_review_content"]['show_id'], "show_name");

//$content_arr = array(
//    "content" => $_SESSION['edit_review_content'],
//    "value" => $_SESSION['edit_review_value'],
//    "date" => $_SESSION['edit_review_date'],
//    "show_id" => $_SESSION['edit_show_id'],
//    "poster_url" => $poster,
//    "show_name" => $showname['show_name']
//);

// on second thought, wait until review is submitted before calling unset on these
//unset($_SESSION['edit_review_date']);
//unset($_SESSION['edit_review_content']);
//unset($_SESSION['edit_review_value']);
//unset($_SESSION['edit_show_id']);

$page_name = "Editing a review for " . $showname;

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
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="me-md-3 me-xl-5">
                                    <div class="container-md-1" style="float:left; max-width:20vw;">
                                        <div class="card-img-2">
                                            <a href="show_page.php?id=<?php echo $_SESSION[$rid . "_review_content"]['show_id'] ?>">
                                                <img src="<?php echo $poster ?>" width="333"
                                                     height="500"
                                                     alt="Image could not be loaded."/>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex-wrap" style="float:left">
                                        <h2><?php echo $showname ?></h2>
                                        <h4>Last
                                            edited: <?php echo $_SESSION[$rid . "_review_content"]['review_date'] ?></h4>
                                        <form id="form-rev" name="form-rev" action="" method="post">
                                            <textarea id="review_content" name="review_content" rows="5"
                                                      cols="50"
                                                      style="border: 2px solid black; border-radius: 5px; padding: 20px"
                                                      placeholder="Enter your review for <?php echo $page_name ?>..."
                                            ><?php echo $_SESSION[$rid . "_review_content"]['review_content']; ?></textarea>
                                            <br>
                                            <div class="rate flex-wrap" style="margin-left: -0.5vw">
                                                <?php for ($i = 5; $i >= 1; $i--) { ?>
                                                    <input type="radio" id="star<?php echo $i; ?>" name="rate"
                                                           value="<?php echo $i; ?>" <?php if ($_SESSION[$rid . "_review_content"]['review_value'] == $i) echo "checked"; ?>/>
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
    <!-- endinject -->

    <script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>
</html>