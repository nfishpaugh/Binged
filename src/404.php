<?php
include "include/config.inc";
$page_name = "Page not found";
?>
<!DOCTYPE html>
<html lang="en" class="no-mobile">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $page_name; ?></title>
    <link rel="stylesheet" type="text/css" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" type="text/css" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="shortcut icon" href="images/binged_logo.svg"/>
</head>
<body>
<div class="container-scroller">
    <?php require_once "partials/_navbar.php" ?>
    <div class="container-fluid page-body-wrapper">
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="row">
                    <div class="d-flex align-items-center justify-content-center px-2">
                        <div class="text-center">
                            <h1 class="display-1 fw-bold">404</h1>
                            <p class="">The page you were looking for could not be found.</p>
                            <a href="index.php" class="btn btn-primary px-4 py-2">Go Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
