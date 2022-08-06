<?php
require_once ('config.php');
require_once ('vendor/autoload.php');

$con = db_connect();
include_server_error_page($con);
mysqli_set_charset($con, "utf8");
date_default_timezone_set('Europe/Moscow');
