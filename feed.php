<?php
session_start();
if (empty($_SESSION['user'])) {
  header("location: index.php");
}

require_once('helpers.php');
require_once('db_helpers.php');

$con = db_connect();
include_server_error_page($con);

mysqli_set_charset($con, "utf8");

$post_types = db_get_post_types($con);
include_server_error_page($post_types);

$all_tab = 'all';
$tab = $_GET['tab'] ?? $all_tab;
$is_all_tab = $tab === $all_tab;

$posts = db_get_posts($con, $tab, $is_all_tab, null, $_SESSION['user']['id']);

if ($posts === null) {
  include_server_error_page(false);
}

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
