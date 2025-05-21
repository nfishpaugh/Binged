<?php
include "include/config.inc";
include "include/utils.php";

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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete'], $_SESSION[PREFIX . '_user_id']) && $_SESSION[PREFIX . '_user_id'] == $user_info['user_id'] && $_SESSION[PREFIX . '_security'] > 1) {
    $mysqli->review_delete($rev_id);
    if (!$review_count = $mysqli->get_show_column($show_id, "review_amt")) {
        $review_count = 0;
    }
    update_avg($show_id, $review['review_value'], $review_count, $mysqli, true);
    header("location: show_page.php?id=" . $show_id);
    exit;
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && (!isset($_SESSION[PREFIX . '_user_id']) || $_SESSION[PREFIX . '_user_id'] !== $user_info['user_id'])) {
    ?>
    <script>
        alert("Invalid user id");
    </script><?php
    $_POST = array();
}

if (isset($user_pf['profile_pic_src'])) {
    $pfp = $user_pf['profile_pic_src'];
} else {
    $pfp = 'dummy_pfp.jpg';
}

$page_name = $user_info['user_name'] . "'" . "s review of " . $show_info['show_name'];

//$year = substr($show_info['show_air_date'], 0, 4);

$r_str = starify($review["review_value"]);

// don't need to display full timestamp, just the date
$d = date_create($review['review_date']);
$d = date_format($d, "d M Y");

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
                <!-- TODO - Fix responsive issues, image is cut off and the title of the show stacks on top of itself -->
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body" style="margin-left: auto; margin-right: auto; min-width: 75%;">
                                <div class="container-md">
                                    <div class="card-img-2" style="display: block;">
                                        <a href="show_page.php?id=<?php echo $show_id ?>">
                                            <img src="<?php echo $img_url . $show_info['show_poster_path'] ?>"
                                                 style="object-fit: scale-down; max-height: 100%; max-width: 100%;"
                                                 alt="Image could not be loaded."/>
                                        </a>
                                    </div>
                                    <div class="container-md"
                                         style="word-break: break-all; display: block; max-width: 600px; min-width: 300px;">
                                        <header style="min-width: 250px; max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                            <a class="one" href="user_profile.php?id=<?php echo $user_id ?>">
                                                <h4 style="padding-bottom: 10px;">
                                                    <img src="images/faces/<?php echo $pfp ?>"
                                                         style="width: 25px; height: 25px; border-radius: 100%;"/>
                                                    <?php echo " Review by " . $user_info['user_name'] ?>
                                                </h4>
                                            </a>
                                        </header>
                                        <a class="one" href="show_page.php?id=<?php echo $show_id ?>">
                                            <h2 style="padding-bottom: 15px; line-height: 1; min-width: 300px;"><?php echo $show_info['show_name']; ?></h2>
                                        </a>
                                        <div class="card-description" style="min-width: 300px;">
                                            <span style="padding-top: 20px; padding-bottom: 20px; line-height: 0.5; color: #0072ff; font-size: 22px; display: block"><?php echo $r_str; ?></span>
                                            <h5 style="color: darkgrey"><?php echo "Watched on " . $d; ?></h5>
                                            <p style="padding-bottom: 10px; padding-top: 10px; color: black;"><?php echo $review['review_content']; ?></p>
                                        </div>
                                        <div class="container-sm" style="display: block; ">
                                            <?php if (isset($user_info['user_id']) && $user_info['user_id'] == $_SESSION[PREFIX . '_user_id']) { ?>
                                                <form method="POST" action="">
                                                    <button class="d-inline btn btn-primary mt-2 mt-xl-0" name="edit">
                                                        Edit
                                                    </button>
                                                    <button class="d-inline btn btn-primary mt-2 mt-xl-0" name="delete">
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    </div>
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
<!-- End plugin js for this page-->
<!-- Custom js for this page-->


<!-- End custom js for this page-->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>
</html>

