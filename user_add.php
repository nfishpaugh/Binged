<!DOCTYPE html>
<html lang="en">

<?php
include "include/config.inc";

$page_name = "User Add";

if (isset($_POST['back'])) {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && !$mysqli->user_field_check($_POST['user_email'], "email") && !$mysqli->user_field_check($_POST['user_name'], "user_name")) {
    //var_dump($_POST);

    $uid = $mysqli->user_insert($_POST['user_email'], $_POST['user_name'], $_POST['user_password'], 5);

    // Generate profile data
    $mysqli->user_pf_insert($uid, date('Y-m-d'));

    $mysqli->actions_insert("Added User: " . $_POST['user_email'], $uid);

    $_SESSION[PREFIX . '_action'][] = 'added';

    header("location: login.php");
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === "POST" && $mysqli->user_field_check($_POST['user_email'], "email")) {
    ?>
    <script> alert("Email has been taken, please choose another."); </script>
<?php
$_POST = array();
} elseif ($_SERVER['REQUEST_METHOD'] === "POST" && $mysqli->user_field_check($_POST['user_name'], "user_name")) {
?>
    <script> alert("User name has been taken, please choose another."); </script>
    <?php
    $_POST = array();
}
?>

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
    <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/binged_logo.svg"/>

</head>

<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
            <div class="row w-100 mx-0">
                <div class="col-lg-4 mx-auto">
                    <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                        <div class="brand-logo" style="margin-bottom: 0;">
                            <img src="images/binged_logo.svg" width="64" height="64" alt="logo">
                        </div>
                        <h6 class="font-weight-light">Enter your information below to create a Binged account</h6>
                        <form id="subform" class="pt-3" action="" method="POST">
                            <div class="form-group">
                                <input type="email" class="form-control form-control-lg" id="user_email"
                                       name="user_email"
                                       placeholder="Email" autofocus>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control form-control-lg" id="user_name"
                                       name="user_name" placeholder="User name">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-lg" id="user_password"
                                       name="user_password" onkeyup="check()"
                                       placeholder="Password">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-lg" id="confpassword"
                                       name="confpassword" onkeyup="check()"
                                       placeholder="Confirm password">
                                <span id="passwordmsg"></span>
                            </div>
                            <div class="mt-3">
                                <button type="submit" id="sub" name="sub"
                                        class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn"
                                        value="SUBMIT">Submit
                                </button>
                                <button type="submit" id="back" name="back"
                                        class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn"
                                        style="float: right"
                                        value="BACK">Back
                                </button>
                            </div>
                            <script>
                                let check = function () {
                                    if (document.getElementById('user_password').value !== document.getElementById('confpassword').value) {
                                        document.getElementById('passwordmsg').style.color = 'red';
                                        document.getElementById('passwordmsg').innerHTML = 'Passwords do not match.';
                                        document.getElementById('sub').prop('disabled', true);
                                    } else {
                                        document.getElementById('passwordmsg').innerHTML = '';
                                        document.getElementById('sub').prop('disabled', false);
                                    }
                                }
                            </script>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

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
<!-- Custom js for this page-->


<!-- End custom js for this page-->

<script src="js/jquery.cookie.js" type="text/javascript"></script>
</body>

</html>

