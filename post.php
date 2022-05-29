<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');

$con = $con ?? null;

$id = $_GET['id'] ?? '';
include_not_found_page($id);

$post = db_get_post($con, $id);
include_server_error_page($post);

$post_author_id =  $post['author_id'] ?? '';
$user = db_get_user($con, $post_author_id);
include_server_error_page($user);

$page_content = include_template('post-details.php', [
  'post' => $post,
  'user' => $user,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme Пост',
  'content' => $page_content,
  'user' => $_SESSION['user'],
]);

print($layout_content);