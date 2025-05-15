<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

// Only define constant once, otherwise warnings will populate the page
if (!defined("PREFIX")) define('PREFIX', 'boiler');

//THIS IS JUST TO REDUCE PHP ERRORS
date_default_timezone_set("America/Chicago");

//SET UP AND START PHP SESSION
if (!session_start()) {
    session_name(PREFIX);

    // SAVES SESSION IN MAIN DIR
    session_save_path("./");

    // SET SESSION TIMEOUT
    ini_set('session.gc_maxlifetime', 720);
    session_start();
}

$_SESSION[PREFIX . '_created'] = time();
$_SESSION[PREFIX . '_last_activity'] = time();

$app_name = "Binged";

//CREATE VARIABLES FOR ANY EXTERNAL FILES THAT MAY NEED TO BE INCLUDED ON MULTIPLE PAGES
//SO IF THE FILE NEEDS TO BE UPDATED, IT ONLY HAS TO BE UPDATED IN 1 PLACE

//INCLUDE ANY MYSQLI OR ORACLE CLASS HERE:
require_once('mysqli_class.php');
$mysqli = new mysqli_class();