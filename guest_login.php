<?php
include "include/config.inc";
include "login.php";

$_SESSION[PREFIX . "_ppage"] = '';

$response = $mysqli->login("guest@gmail.com", "Guest");
setlogin($response);
