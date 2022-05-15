<?php
session_start();
if (!empty($_SESSION['user'])) {
  header("location: popular.php");
}

require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');

$errors = [];

if (count($_POST) > 0) {
  $errors = get_errors_login_form($_POST);

  if (count($errors) === 0) {
    $con = db_connect();
    include_server_error_page($con);
    mysqli_set_charset($con, "utf8");

    $user = db_get_login_user($con, $_POST['login']);

    if ($user === null) {
      $errors['login'] = [
        'error' => 'Неверный логин'
      ];
    } elseif (!password_verify($_POST['password'], $user['password'])) {
      $errors['password'] = [
        'error' => 'Пароли не совпадают'
      ];
    } else {
      $_SESSION['user'] = [
        'id' => $user['id'],
        'login' => $user['login'],
        'avatar' => $user['avatar'],
      ];

      header("location: feed.php");
    }
  }
}

$layout_content = include_template('main.php', [
  'errors' => $errors,
  'login_value' => count($errors) > 0 ? htmlspecialchars($_POST['login'] ?? '') : '',
]);

print($layout_content);