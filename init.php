<?php
$con = db_connect();
include_server_error_page($con);
mysqli_set_charset($con, "utf8");
$unread_messages = 0;

if (isset($_SESSION['user']['id'])) {
  $unread_messages = db_get_unread_message_count($con, $_SESSION['user']['id']);
}

require_once ('vendor/autoload.php');
date_default_timezone_set('Europe/Moscow');
