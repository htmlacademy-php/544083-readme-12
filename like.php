<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');

$con = $con ?? null;

$user_id = $_SESSION['user']['id'] ?? null;
$post_id = $_GET['post'] ?? null;
include_not_found_page($user_id && $post_id);

$add_like = db_add_post_like($con, $user_id, $post_id);
include_server_error_page($add_like);

header("location: {$_SERVER['HTTP_REFERER']}");