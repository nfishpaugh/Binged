<?php
include "include/config.inc";


if ($_POST['email'] != "" && $_POST['password'] != "") {


    $login_response = $mysqli->login($_POST['email'], $_POST['password']);
    if ($login_response[0] == 1) {
        $_SESSION[PREFIX . '_username'] = $login_response[1]['email'];
        $_SESSION[PREFIX . '_user_id'] = $login_response[1]['user_id'];
        $_SESSION[PREFIX . '_security'] = $login_response[1]['user_level_id'];
        $_SESSION[PREFIX . '_fullname'] = $login_response[1]['user_name'];

        if ($_SESSION[PREFIX . "_ppage"] != '') {
            $redirect = $_SESSION[PREFIX . "_ppage"];
            header("location: $redirect");
            exit;
        }
        header("location:index.php");
        exit;
    } else {
        $loginF = "You are not approved to access this site";
    }

}//END POST

//echo $_SESSION[PREFIX."_ppage"];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Telephile</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png"/>
</head>

<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
            <div class="row w-100 mx-0">
                <div class="col-lg-4 mx-auto">
                    <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                        <div class="brand-logo" style="margin-bottom: 0;">
                            <img src="images/logocustom.jpg" alt="logo">
                        </div>
                        <h4>Hello! Welcome to Telephile!</h4>
                        <h6 class="font-weight-light">Sign in to continue.</h6>
                        <form class="pt-3" action="" method="POST">
                            <div class="form-group">
                                <input type="email" class="form-control form-control-lg" id="email" name="email"
                                       placeholder="Username" required autofocus>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-lg" id="password"
                                       name="password"
                                       placeholder="Password" required>
                            </div>
                            <div class="mt-3">
                                <input type="submit" id="submit"
                                       class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn"
                                       value="SIGN IN">
                            </div>

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
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<!-- endinject -->
</body>

</html>
