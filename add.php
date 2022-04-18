<?php
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');
require_once('enums.php');

date_default_timezone_set('Europe/Moscow');

$is_auth = true;
$user_name = 'Alexandr';

$con = db_connect();

include_server_error_page($con);

mysqli_set_charset($con, "utf8");

$post_types = db_get_post_types($con);
include_server_error_page($post_types);

$tab = $_GET['tab'] ?? $_POST['type'] ?? 'photo';

$types = array_column($post_types, 'type', 'id');
include_not_found_page(in_array($tab, $types, true));

$errors = [];
$values = [];

if (count($_POST) > 0) {
  $errors = get_errors_post_form($_POST, $_FILES);
  if (count($errors) === 0) {
    $move_file = move_download_file($_FILES['post-photo'] ?? [], 'img');
    if ($move_file === false) {
      $errors['post-photo'] = [
        'error' => 'Не удалось загрузить изображение',
        'label' => 'Фото'
      ];
    } elseif ($move_file === null) {
      $put_file = put_link_file($_POST['photo-url'] ?? '', 'img');
      if ($put_file === false) {
        $errors['photo-url'] = [
          'error' => 'Не удалось скачать файл',
          'label' => 'Ссылка',
        ];
      }
    }

    if (count($errors) === 0) {
      $type_id = array_search($_POST['type'] ?? [], $types);
      $post_id = db_add_post($con, $_POST, $_FILES, $type_id);
      include_server_error_page($post_id);
      header("location: post.php?id=$post_id");
    }
  }

  if (count($errors) !== 0) {
    foreach($_POST as $key => $post) {
      $values[$key] = htmlspecialchars($post);
    }
  }
}

$page_content = include_template('adding-post.php', [
  'post_types' => $post_types,
  'tab' => $tab,
  'errors' => $errors,
  'values' => $values,
]);

$layout_content = include_template('layout.php', [
  'title' => 'Добавление поста',
  'is_auth' => $is_auth,
  'user_name' => $user_name,
  'content' => $page_content,
]);

print($layout_content);
