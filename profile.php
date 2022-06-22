<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');

$con = $con ?? null;

$user_id = $_GET['id'] ?? null;
include_not_found_page($user_id && (int)($user_id));

$user = db_get_user($con, $user_id);
include_not_found_page($user);

$current_user = db_get_user($con, $_SESSION['user']['id']);
include_server_error_page($current_user);

$posts = db_get_posts($con, 'all', 'true', null, $user_id);
include_server_error_page(is_array($posts));

$page_content = include_template('profile-content.php', [
  'user' => $user,
  'posts' => $posts,
  'current_user' => $current_user,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: профиль',
  'content' => $page_content,
  'user' => $_SESSION['user'],
]);

print($layout_content);
