<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');

$con = $con ?? null;

$post_types = db_get_post_types($con);
include_server_error_page($post_types);

$subscriptions = db_get_subscriptions($con, $_SESSION['user']['id']);
include_server_error_page(is_array($subscriptions));

$followings = array_filter($subscriptions, function($item) {
  return $item['isFollowing'];
});

$followings = array_column($followings, 'id');

$all_tab = 'all';
$tab = $_GET['tab'] ?? $all_tab;
$is_all_tab = $tab === $all_tab;

$posts = db_get_posts($con, $tab, $is_all_tab, null, $followings);
include_server_error_page(is_array($posts));

$page_content = include_template('my-posts.php', [
  'user' => $_SESSION['user'],
  'posts' => $posts,
  'post_types' => $post_types,
  'is_all_tab' => $is_all_tab,
  'tab' => $tab,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: моя лента',
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'page' => 'feed',
]);

print($layout_content);
