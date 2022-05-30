<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');

$con = $con ?? null;

$post_types = db_get_post_types($con);
include_server_error_page($post_types);

$all_tab = 'all';
$tab = $_GET['tab'] ?? $all_tab;
$is_all_tab = $tab === $all_tab;

$sort = $_GET['sort'] ?? 'views';

$posts = db_get_posts($con, $tab, $is_all_tab, $sort);
include_server_error_page($posts);

$page_content = include_template('post-list.php', [
  'post_types' => $post_types,
  'posts' => $posts,
  'tab' => $tab,
  'sort' => $sort,
  'is_all_tab' => $is_all_tab,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: популярное',
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'page' => 'popular',
]);

print($layout_content);
