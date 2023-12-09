<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}

$in_id = (int)$_GET['id'];
if (!$in_id) {
    header("location: show_list.php");
    exit;
}
$show_info = $mysqli->show_info($in_id);

$img_url = $mysqli->tmdb_api($show_info['show_name']);

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
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <div style="float:left">
                                        <img src="<?php echo $img_url ?>" width="333" height="500"
                                             style="margin-right:50px" alt="Image could not be loaded."/>
                                    </div>
                                    <div style="float:right padding-top: 80px">
                                        <h2 style="flex-wrap"><?php echo $page_name; ?></h2>
                                        <p style="flex-wrap"><?php echo $show_info['description']; ?></p>
                                        <p>Year released: <?php echo $show_info['year']; ?></p>
                                        <p>Runtime: <?php
                                            if (!is_null($show_info['runtime'])) {
                                                echo $show_info['runtime'];
                                            } else {
                                                echo "Not Available";
                                            } ?></p>
                                        <p>Votes: <?php echo $show_info['votes'] ?></p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end flex-wrap"
                                         style="float:right">

                                        <a href="show_review.php?id=<?php echo $in_id ?>"
                                           class="btn btn-primary mt-2 mt-xl-0"><i
                                                    class="mdi mdi-plus-circle-outline btn-icon-prepend"></i>Write a
                                            review</a>
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
</div>
</div>
</body>
</html>


