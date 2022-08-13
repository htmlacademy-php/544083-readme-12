<?php
session_start();
if (!empty($_SESSION['user'])) {
  header("location: popular.php");
}

require_once ('config.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');
require_once('validator.php');

$errors = [];

if (count($_POST) > 0) {
  $errors = get_errors_login_form($_POST);

  if (count($errors) === 0) {
    $con = $con ?? null;

    $user = db_get_login_user($con, $_POST['login']);

    if ($user === null) {
      $errors['login'] = [
        'error' => 'Неверный логин или пароль'
      ];
    } elseif (!password_verify($_POST['password'], $user['password'])) {
      $errors['password'] = [
        'error' => 'Неверный логин или пароль'
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