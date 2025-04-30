<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = 'index.php';

$_SESSION[PREFIX . '_username'] = "guest@gmail.com";
$_SESSION[PREFIX . '_user_id'] = 0;
$_SESSION[PREFIX . '_security'] = 1;
$_SESSION[PREFIX . '_fullname'] = 'Guest';
ini_set('session.gc_maxlifetime', 240);

header("location: index.php");
exit;