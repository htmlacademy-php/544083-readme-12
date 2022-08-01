<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');
require_once('init.php');

$con = $con ?? null;

$current_dialog_user_id = null;
$current_dialog_user = [];
$current_dialog = [];
$error = [];

$dialogs = db_get_dialogs($con, $_SESSION['user']['id']);
include_server_error_page(is_array($dialogs));

$current_dialog_user_id = $_GET['id'] ?? $dialogs[0]['id'] ?? null;
$current_dialog_user_id = (int) $current_dialog_user_id;

if ($current_dialog_user_id) {
  include_not_found_page(db_user_exist($con, $current_dialog_user_id));

  $current_dialog_user = db_get_user($con, $current_dialog_user_id);
  include_server_error_page($current_dialog_user);

  $set_read_current_dialog = db_set_read_messages($con, $current_dialog_user_id, $_SESSION['user']['id']);
  include_server_error_page($set_read_current_dialog);

  $current_dialog = db_get_dialog($con, $current_dialog_user_id, $_SESSION['user']['id']);
  include_server_error_page(is_array($current_dialog));

  $is_current_dialog_user_dialogs_exist = false;

  foreach ($dialogs as $dialog) {
    if ($dialog['id'] === $current_dialog_user_id) {
      $is_current_dialog_user_dialogs_exist = true;
      break;
    }
  }

  if (!$is_current_dialog_user_dialogs_exist) {
    array_unshift($dialogs, [
      'id' => $current_dialog_user['id'],
      'login' => $current_dialog_user['login'],
      'avatar' => $current_dialog_user['avatar'],
    ]);
  }

  if (isset($_POST['message-content'])) {
    $error = validate_text_field($_POST['message-content'], 'Ошибка валидации', true);

    if (!$error) {
      $add_message = db_add_message($con, $_SESSION['user']['id'], $current_dialog_user_id, $_POST['message-content']);
      include_server_error_page($add_message);
      header("Refresh:0");
    }
  }
}


$page_content = include_template('messages-content.php', [
  'dialogs' => $dialogs,
  'current_user' => $_SESSION['user'],
  'current_dialog_user' => $current_dialog_user,
  'current_dialog' => $current_dialog,
  'error' => $error,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: сообщения',
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'unread_messages' => $unread_messages ?? 0,
]);

print($layout_content);