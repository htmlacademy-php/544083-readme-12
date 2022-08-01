<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');
require_once('init.php');
require_once ('send-mail.php');

$con = $con ?? null;
$post_types = db_get_post_types($con);
include_server_error_page($post_types);

$tab = $_GET['tab'] ?? $_POST['type'] ?? 'photo';

$types = array_column($post_types, 'type', 'id');
include_not_found_page(in_array($tab, $types, true));

$errors = [];
$values = [];

if (count($_POST) > 0) {
  $errors = get_errors_post_form($_POST, $_FILES);
  if (isset($_POST['type']) && $_POST['type'] === 'photo') {
    $move_file = move_download_file($_FILES['post-photo'] ?? []);
    if ($move_file === '') {
      $errors['post-photo'] = [
        'error' => 'Не удалось загрузить изображение',
        'label' => 'Фото'
      ];
    } elseif ($move_file === null) {
      $put_file = put_link_file($_POST['photo-url'] ?? '');
      if (!(bool)$put_file) {
        $errors['photo-url'] = [
          'error' => 'Не удалось скачать файл',
          'label' => 'Ссылка',
        ];
      }
    }
  }

  if (count($errors) === 0) {
    $type_id = array_search($_POST['type'] ?? [], $types);
    $post_id = db_add_post($con, $_POST, $move_file ?? '', $put_file ?? '', $type_id, $_SESSION['user']['id']);
    include_server_error_page($post_id);
    $followers = db_get_followers($con, $_SESSION['user']['id']);
    if (is_array($followers) && count($followers) > 0) {
      $subject = "Новая публикация от пользователя {$_SESSION['user']['login']}";
      $href = "http://readme/profile.php?id={$_SESSION['user']['id']}";
      $link = sprintf('<a href="%s">%s</a>', $href, $href);
      foreach ($followers as $follower) {
        $body = "Здравствуйте, {$follower['login']}. Пользователь {$_SESSION['user']['login']} только что опубликовал новую запись „{$_POST['post-title']}“. Посмотрите её на странице пользователя: $link";
        send_mail($follower['email'], $subject, $body);
      }
    }
    header("location: post.php?id=$post_id");
  } else {
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
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'unread_messages' => $unread_messages ?? 0
]);

print($layout_content);
