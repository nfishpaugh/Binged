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

if (!($review = $mysqli->review_info($rev_id)) || !($show_info = $mysqli->show_info($show_id)) || !($user_info = $mysqli->user_info($user_id))) {
    header("location: index.php");
    exit;
}

$img_url = 'https://image.tmdb.org/t/p/original';

$user_pf = $mysqli->user_pf_info($user_id);

if ($_SERVER["REQUEST_METHOD"] === "POST" && (!isset($_SESSION[PREFIX . '_user_id']) || (isset($_SESSION[PREFIX . '_user_id']) && $_SESSION[PREFIX . '_user_id'] !== $user_info['user_id']) || $_SESSION[PREFIX . '_security'] <= 1)) {
    unset($_POST);
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete'])) {
    $mysqli->review_delete($rev_id);
    if (!$review_count = $mysqli->get_show_column($show_id, "review_amt")) {
        $review_count = 0;
    }
    update_avg($show_id, $review['review_value'], $review_count, $mysqli, true);
    header("location: show_page.php?id=" . $show_id);
    exit;
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['modal-edit'])) {
    $rating = match ($_POST['rate']) {
        "5" => 5,
        "4" => 4,
        "3" => 3,
        "2" => 2,
        default => 1,
    };

    $mysqli->review_update($rev_id, $rating, $_POST['review-input']);

    // only update the average if the rating changes
    if ($rating != $review['review_value']) {
        $review_count = $mysqli->get_show_column($show_id, "review_amt");

        // can just set the avg to the user's rating if it's the only review
        if (!$review_count || $review_count === 1) {
            $mysqli->update_show_column($show_id, $rating, "review_avg");
            $mysqli->update_show_column($show_id, 1, "review_amt");
        } else {
            update_avg($show_id, $rating, $review_count, $mysqli, false, true, $review['review_value']);
        }
    }

    $mysqli->actions_insert("Updated Review: " . date("Y-m-d") . " " . $show_id, $_SESSION[PREFIX . '_user_id']);

    $_SESSION[PREFIX . '_action'][] = 'updated';
    header("location: review_page.php?rid=" . $rev_id . "&sid=" . $show_id . "&uid=" . $user_id);
    exit;
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
$d = date_format($d, "d-M-Y");

$temp_url = $img_url . $show_info['show_poster_path'];

?>
<!DOCTYPE html>
<html lang="en" class="no-mobile">

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
                            <div class="card-body" style="margin-left: auto; margin-right: auto; min-width: 75%;">
                                <div class="container-md">
                                    <div class="card-img-2" style="display: block;">
                                        <a href="show_page.php?id=<?php echo $show_id ?>">
                                            <img src="<?php echo $temp_url; ?>"
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
                                                    <button type="button" class="d-inline btn btn-primary mt-2 mt-xl-0"
                                                            name="edit" data-bs-toggle="modal"
                                                            data-bs-target="#review-modal" id="modal_open_review">
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

<div class="modal fade reviewModal hideModal" id="review-modal" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="height: 60%">
            <div class="modal-header">
                <h5 class="modal-title" id="review-modal-title">
                    <span id="review-modal-title-edit" data-title-label="edit">Edit review</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="modal_close_review"></button>
            </div>
            <div class="modal-body">
                <form id="review-form" class="reviewForm" method="post">
                    <section class="reviewStep submit fields-reversed">
                        <header class="header">
                            <div class="prod inline film">
                                <h3 class="primaryname">
                                    <span class="name"><?php echo $show_info['show_name']; ?></span>
                                </h3>
                            </div>
                        </header>
                        <aside class="figure">
                            <figure>
                                <img src="<?php echo $temp_url; ?>" width="150" height="225" alt=""/>
                            </figure>
                        </aside>
                        <div class="body">
                            <div class="reviewfields">
                                <div class="inner">
                                    <input type="hidden" name="modal-edit" id="modal-edit-hidden" value="1">
                                    <textarea id="review-input" class="field reviewfield" name="review-input"
                                              placeholder="Add a review..."
                                              autofocus><?php echo $review['review_content']; ?></textarea>
                                    <span id="review-input-msg" class="alert-msg"></span>
                                </div>
                            </div>
                            <div class="rating">
                                <div class="rate">
                                    <h4 class="rating-label">Rating: </h4>
                                    <?php for ($i = 5; $i >= 1; $i--) { ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rate"
                                               value="<?php echo $i; ?>" <?php if ($i == $review['review_value']) echo "checked"; ?>/>
                                        <label for="star<?php echo $i; ?>" title="text"><?php echo $i; ?>
                                            stars</label>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit" form="review-form" id="review-modal-submit-btn">Submit
                </button>
            </div>
        </div>
    </div>
</div>
<!-- plugins:js -->
<script src="vendors/base/vendor.bundle.base.js"></script>
<script src="js/eventListeners.js"></script>
<script>
    // review modal basic form validation
    document.getElementById('review-modal-submit-btn').addEventListener('click', function (evt) {
        let el = document.getElementById('review-input-msg');
        let revEl = document.getElementById('review-input');
        if (revEl.value === '' || revEl.value.length < 5) {
            evt.preventDefault();
            el.style.color = 'red';
            el.innerText = 'Review text must be at least 5 characters long.';
            this.prop('disabled', true);
        } else if (revEl.value.length > 1000) {
            evt.preventDefault();
            el.style.color = 'red';
            el.innerText = 'Review text cannot be over 1000 characters.';
            this.prop('disabled', true);
        } else {
            el.innerText = '';
            this.prop('disabled', true);
        }
    });

    document.getElementById('modal_close_review').addEventListener('click', function () {
        changeModalDisplay(document.getElementById('review-modal'));
    });

    document.getElementById('modal_open_review').addEventListener('click', function () {
        changeModalDisplay(document.getElementById('review-modal'));
    });
</script>
<!-- endinject -->
<!-- Plugin js for this page-->
<!-- End plugin js for this page-->
<!-- Custom js for this page-->


<!-- End custom js for this page-->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>
</html>

