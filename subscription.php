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

$sql = "INSERT INTO subscriptions (following_id, follower_id) VALUES ($following_id, $follower_id)";

$result = mysqli_query($con, $sql);

if (!$result) {
  $sql = "DELETE FROM subscriptions WHERE following_id = $following_id AND follower_id = $follower_id";
  $result = mysqli_query($con, $sql);
}
include_server_error_page($result);

header("location: {$_SERVER['HTTP_REFERER']}");