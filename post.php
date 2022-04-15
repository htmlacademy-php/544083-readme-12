<?php
require_once('helpers.php');
require_once('db_helpers.php');

date_default_timezone_set('Europe/Moscow');

$con = mysqli_connect("localhost", "root", "","readme");

if ($con === false) {
  print("Ошибка подключения: " . mysqli_connect_error());
  die;
}

mysqli_set_charset($con, "utf8");

$id = $_GET['id'] ?? '';
include_not_found_page(boolval($id));

$post = db_get_post($con, $id);
include_not_found_page(boolval($post));

$post_author_id =  $post['author_id'] ?? '';
$user = db_get_user($con, $post_author_id);
include_not_found_page(boolval($user));

$page_content = include_template('post-details.php', [
  'post' => $post,
  'user' => $user,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme Пост',
  'content' => $page_content,
]);

print($layout_content);