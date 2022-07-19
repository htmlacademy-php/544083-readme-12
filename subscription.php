<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');
require_once ('send-mail.php');

$con = $con ?? null;

$following_id = db_user_exist($con, $_GET['id']);
include_server_error_page($following_id);

$follower_id = $_SESSION['user']['id'];
$result = false;

if (db_is_following($con, $following_id, $follower_id)) {
  $result = db_delete_following($con, $following_id, $follower_id);
} else {
  $result = db_add_following($con, $following_id, $follower_id);

  if ($result) {
    $following = db_get_user($con, $following_id);
    if ($following) {
      $subject = "У вас новый подписчик";
      $href = "http://readme/profile.php?id={$_SESSION['user']['id']}";
      $link = sprintf('<a href="%s">%s</a>', $href, $href);
      $body = "Здравствуйте, {$following['login']}. На вас подписался новый пользователь {$_SESSION['user']['login']}. Вот ссылка на его профиль: $link";
      send_mail( $following['email'], $subject, $body);
    }
  }
}

include_server_error_page($result);

header("location: {$_SERVER['HTTP_REFERER']}");