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

$user_pf_info = $mysqli->user_pf_info($in_id);
$review_count = $mysqli->user_review_count($in_id);
$user_info = $mysqli->user_info($in_id);
$pf_img = $user_pf_info['profile_pic_src'] ?? 'dummy_pfp.jpg';
$pf_desc = $user_pf_info['user_description'];
$pf_name = $user_info['user_name'];
$pf_joined = $user_pf_info['user_join_date'];
$page_name = $pf_name . "'s profile";
$img_url = 'https://image.tmdb.org/t/p/original';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $page_name; ?></title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/binged_logo.svg"/>

</head>
<body>
<div class="container-scroller">
    <?php require_once 'partials/_navbar.php'; ?>
    <div class="container-fluid page-body-wrapper">
        <div class="main-panel">
            <div class="content-wrapper">

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="dashboard-tabs p-0">
                            <ul class="nav nav-tabs px-4">
                                <li class="nav-item active">
                                    <a id="profile-tab" class="nav-link active" href="#profile">Profile</a>
                                </li>
                                <li class="nav-item">
                                    <a id="review-tab" class="nav-link "
                                       href="user_reviews.php?id=<?php echo $in_id; ?>"><?php echo $pf_name; ?>'s
                                        Reviews (<?php echo $review_count; ?>)</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters mx-lg-5 mb-lg-2">
                    <div class="col-md-12 grid-margin stretch-card" style="height: 100%">
                        <div class="card flex-wrap" id="profile" style="flex-direction: row; padding-bottom: 0;">
                            <div class="card-body">
                                <img src="images/faces/<?php echo $pf_img; ?>"
                                     style="border-radius: 100%" height="150vh" width="150vw"
                                     alt="Profile picture could not be loaded."/>
                            </div>
                            <div class="card-body mt-3" style="width: 60%">
                                <h2 style="color: grey"><?php echo $pf_name; ?></h2>
                                <p class="mt-4 flex-wrap"
                                   style="text-wrap: normal; overflow:visible; white-space: normal; width: 100%; height: 50%"><?php echo $pf_desc; ?></p>
                                <h6 style="color:grey">Joined <?php echo $pf_joined; ?></h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters mb-lg-2">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <h5 style="border-bottom:1px solid grey; text-align:left; padding-bottom:10px">
                                        Recent reviews</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php
                    $results = $mysqli->user_review_info_lim($in_id, 6);
                    if (empty($results)) { ?>
                        <div class="col-sm-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title">No reviews found
                                        for <?php echo $user_info['user_name'] ?></div>
                                </div>
                            </div>
                        </div>
                    <?php } else {
                        foreach ($results as $result) {
                            $temp_url = $img_url . $result['show_poster_path']; ?>
                            <div class="col-6 col-sm-6 col-md-3 col-lg-2 grid-margin stretch-card"
                                 style="border-radius: 0">
                                <div class="card flex-wrap" style="border-radius: 0;">
                                    <div class="container-lg">
                                        <div class="card-img">
                                            <a href="review_page.php?rid=<?php echo $result['review_id']; ?>&sid=<?php echo $result['id']; ?>&uid=<?php echo $in_id; ?>"><img
                                                        src="<?php echo $temp_url ?>" class="card-img"
                                                        style="max-width: 100%; max-height: 100%; object-fit: scale-down"
                                                        alt=""/></a>
                                        </div>
                                    </div>

                                    <div class="card-description">
                                        <a href="show_page.php?id=<?php echo $result['id']; ?>"
                                           style="text-decoration: none; color: inherit">
                                            <p class="card-title"
                                               style="flex-wrap: wrap; white-space: normal; overflow: visible; height: 6vh"><?php echo $result['show_name'] ?></p>
                                            <!-- <p class="card-text" style=""><?php //echo $result['show_overview']; ?></p> -->
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
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