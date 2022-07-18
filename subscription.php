<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');

$con = $con ?? null;

$following_id = db_user_exist($con, $_GET['id']);
include_server_error_page($following_id);

$follower_id = $_SESSION['user']['id'];
$result = false;

if (db_is_following($con, $following_id, $follower_id)) {
  $result = db_delete_follower($con, $following_id, $follower_id);
} else {
  $result = db_add_following($con, $following_id, $follower_id);
}

include_server_error_page($result);

header("location: {$_SERVER['HTTP_REFERER']}");