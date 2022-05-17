<?php

require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');

date_default_timezone_set('Europe/Moscow');

$con = db_connect();
include_server_error_page($con);

mysqli_set_charset($con, "utf8");

$errors = [];
$values = [];

if (count($_POST) > 0) {
  $errors = get_errors_join_form($_POST, $_FILES['userpic-file'] ?? []);

  if (count($errors) === 0) {
    $move_file = move_download_file($_FILES['userpic-file'] ?? []);
    if ($move_file === '') {
      $errors['userpic-file'] = [
        'error' => 'Не удалось загрузить изображение',
        'label' => 'Фото'
      ];
    } else {
      $email = mysqli_real_escape_string($con, $_POST['email']);
      $login = mysqli_real_escape_string($con, $_POST['login']);
      $sql_email = "SELECT email FROM users WHERE email = '$email'";
      $sql_login = "SELECT login FROM users WHERE login = '$login'";

      $sql_email_res = mysqli_query($con, $sql_email);

      if ($sql_email_res === false) {
        include_server_error_page($sql_email_res);
      } else {
        if (count(mysqli_fetch_all($sql_email_res, MYSQLI_ASSOC)) > 0) {
          $errors['email'] = [
            'label' => 'Электронная почта',
            'error' => 'Такой Email уже зарегестрирован'
          ];
        }
      }

      $sql_login_res = mysqli_query($con, $sql_login);

      if ($sql_login_res === false) {
        include_server_error_page($sql_login_res);
      } else {
        if (count(mysqli_fetch_all($sql_login_res, MYSQLI_ASSOC)) > 0) {
          $errors['login'] = [
            'label' => 'Логин',
            'error' => 'Такой логин уже зарегестрирован'
          ];
        }
      }
    }
  }

  if (count($errors) !== 0) {
    foreach($_POST as $key => $post) {
      $values[$key] = htmlspecialchars($post);
    }
  } else {
     $add_user = db_add_user($con, $_POST, $move_file ?? '');
     include_server_error_page($add_user);
     header("location: popular.php");
  }
}

$page_content = include_template('registration-form.php', [
  'errors' => $errors,
  'values' => $values,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: регистрация',
  'content' => $page_content,
]);

print($layout_content);
