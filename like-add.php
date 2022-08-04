<?php
require_once('session.php');
require_once('init.php');
require_once('helpers.php');
require_once('db_helpers.php');

$con = $con ?? null;

$user_id = $_SESSION['user']['id'];
$post_id = $_GET['post'] ?? null;
include_not_found_page($post_id);

$is_post_liked = db_is_post_liked($con, $user_id, $post_id);
$result = false;

if (!$is_post_liked) {
  $result = db_add_post_like($con, $user_id, $post_id);
} else {
  $result = db_delete_post_like($con, $user_id, $post_id);
}

include_server_error_page($result);

header("location: {$_SERVER['HTTP_REFERER']}");