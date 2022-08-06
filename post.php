<?php
require_once('session.php');
require_once('init.php');
require_once('helpers.php');
require_once('db_helpers.php');

$con = $con ?? null;

$id = $_GET['id'] ?? '';
include_not_found_page($id);

mysqli_begin_transaction($con);
$add_view = db_add_post_view($con, $id);
$post = db_get_post($con, $id);

if ($add_view && $post) {
  mysqli_commit($con);
} else {
  mysqli_rollback($con);
  include_server_error_page(false);
}

$post_author_id =  $post['author_id'] ?? '';
$user = db_get_user($con, $post_author_id);
include_server_error_page($user);

$isFollowing = db_is_following($con, $user['id'], $_SESSION['user']['id']);

$page_content = include_template('post-details.php', [
  'post' => $post,
  'user' => $user,
  'current_user' => $_SESSION['user'],
  'isFollowing' => $isFollowing,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme Пост',
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'unread_messages' => db_get_unread_message_count($con, $_SESSION['user']['id']),
]);

print($layout_content);