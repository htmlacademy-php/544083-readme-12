<?php
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');

date_default_timezone_set('Europe/Moscow');

$is_auth = true;
$user_name = 'Alexandr';

$con = mysqli_connect("localhost", "root", "", "readme");

if ($con === false) {
  print("Ошибка подключения: " . mysqli_connect_error());
  die;
}

mysqli_set_charset($con, "utf8");

$post_types = db_get_post_types($con);
include_not_found_page(boolval($post_types));

$tab = $_GET['tab'] ?? $_POST['type'] ?? 'photo';

$types = array_column($post_types, 'type', 'id');
include_not_found_page(in_array($tab, $types, true));

$errors = [];

if (count($_POST) > 0) {
  $errors = get_errors_post_form();
  $type_id = array_search($_POST['type'] ?? [], $types);
  if (count($errors) === 0) {
    $post_id = db_add_post($con, $type_id);

    if (boolval($post_id)) {
      header("location: post.php?id=$post_id");
    }
  }
}

$page_content = include_template('adding-post.php', [
  'post_types' => $post_types,
  'tab' => $tab,
  'errors' => $errors,
  'values' => count($errors) > 0 ? $_POST : [],
]);

$layout_content = include_template('layout.php', [
  'title' => 'Добавление поста',
  'is_auth' => $is_auth,
  'user_name' => $user_name,
  'content' => $page_content,
]);

print($layout_content);
