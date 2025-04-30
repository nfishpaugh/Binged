<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}
if ($_SESSION[PREFIX . '_security'] < 10) {
    header("location:index.php?action=5");
    exit;
}

$page_name = "User Edit";

$in_id = (int)$_GET['id'];
if (!$in_id) {
    header("location: user_list.php");
    exit;
}
$user_info = $mysqli->user_info($in_id);

if ($_SERVER['REQUEST_METHOD'] == "POST") {


    $mysqli->user_edit($in_id, $_POST['user_email'], $_POST['user_name'], $_POST['user_password'], $_POST['user_level_id']);

    $mysqli->actions_insert("Updated User: " . $_POST['user_email'], $_SESSION[PREFIX . '_user_id']);

    $_SESSION[PREFIX . '_action'][] = 'updated';
    header("location: user_list.php");
    exit;
}//END POST


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $app_name; ?> - <?php echo $page_name; ?></title>
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
        <?php require_once 'partials/_sidebar.php'; ?>
        <div class="main-panel">
            <div class="content-wrapper">

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <h2><?php echo $page_name; ?></h2>
                                    <p class="mb-md-0">text</p>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo $page_name; ?></h4>
                                <p class="card-description">
                                    Horizontal form layout
                                </p>
                                <form class="forms-sample" id="form1" action="" method="post">

                                    <div class="form-group row">
                                        <label for="user_name" class="col-sm-3 col-form-label">Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="user_name"
                                                   name="user_name"
                                                   placeholder="Full Name" autofocus required
                                                   value="<?php echo $user_info['user_name']; ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="user_email" class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="user_email"
                                                   name="user_email"
                                                   placeholder="Email" required
                                                   value="<?php echo $user_info['email']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="user_password" class="col-sm-3 col-form-label">Password</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="user_password"
                                                   name="user_password"
                                                   placeholder="Only populate if you want to change">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="user_level_id" class="col-sm-3 col-form-label">User Level</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="user_level_id"
                                                    name="user_level_id" required>
                                                <?php $results = $mysqli->user_level_list();
                                                foreach ($results as $result) {
                                                    ?>
                                                    <option value="<?php echo $result['user_level_id']; ?>" <?php if ($result['user_level_id'] == $user_info['user_level_id']) {
                                                        echo " selected ";
                                                    } ?>><?php echo $result['user_level_name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>


                                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                                    <button class="btn btn-light">Cancel</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>


            </div>
            <!-- content-wrapper ends -->
            <?php require_once 'partials/_footer.php'; ?>
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

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
<!-- Custom js for this page-->


<!-- End custom js for this page-->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>

</html>

