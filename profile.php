<?php
require_once('session.php');
require_once('init.php');
require_once('helpers.php');
require_once('db_helpers.php');

$con = $con ?? null;

$user_id = $_GET['id'] ?? null;
include_not_found_page($user_id && (int)($user_id));

$tab = $_GET['tab'] ?? null;

$user = db_get_user($con, $user_id);
include_not_found_page($user);

$isFollowing = db_is_following($con, $user['id'], $_SESSION['user']['id']);

$posts = db_get_posts($con, 'all', 'true', 'dt_add', [$user_id]);
include_server_error_page(is_array($posts));

$followings = [];

if ($tab === 'likes') {
  $posts = array_filter(
    $posts,
    static fn (array $item): bool => isset($item['likes']) && count($item['likes'] ?? []) > 0
  );
}

if ($tab === 'subscriptions') {
  $followings = db_get_followings($con, $user['id']);
  include_server_error_page(is_array($followings));

  foreach ($followings as $key => $following) {
    $followings[$key]['isCurrentUserFollowing'] = db_is_following($con, $following['id'], $_SESSION['user']['id']);
  }
}

$page_content = include_template('profile-content.php', [
  'user' => $user,
  'posts' => $posts,
  'current_user' => $_SESSION['user'],
  'followings' => $followings,
  'isFollowing' => $isFollowing,
  'tab' => $tab,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: профиль',
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'unread_messages' => db_get_unread_message_count($con, $_SESSION['user']['id']),
]);

print($layout_content);
