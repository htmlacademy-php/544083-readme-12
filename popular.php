<?php
require_once('session.php');
require_once ('config.php');
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

$page = $_GET['page'] ?? null;

$posts = db_get_posts($con, $tab, $is_all_tab, $sort);
include_server_error_page(is_array($posts));

$posts_count = count($posts);
$max_posts_page = 6;
$limit = $posts_count > 9 || (bool)$page ? $max_posts_page : null;
$offset = $limit ? ((bool)$page ? ($page - 1) * $limit : 0) : null;
$need_pagination = is_int($limit) && is_int($offset);
$prev_page_link = null;
$next_page_link = null;

if ($need_pagination) {
  $posts = db_get_posts($con, $tab, $is_all_tab, $sort, null, $limit, $offset);
  include_server_error_page(is_array($posts));

  $prev_page = (bool)$page && $page > 1 ? $page - 1 : null;
  if ($prev_page) {
    $prev_page_link = '?' . http_build_query(['sort' => $sort, 'tab' => $tab, 'page' => $prev_page]);
  }

  $next_page = (bool)$page ? $page + 1 : 2;
  if (ceil(($posts_count / $max_posts_page)) >= $next_page) {
    $next_page_link = '?' . http_build_query(['sort' => $sort, 'tab' => $tab, 'page' => $next_page]);
  }
}

$page_content = include_template('post-list.php', [
  'post_types' => $post_types,
  'posts' => $posts,
  'tab' => $tab,
  'sort' => $sort,
  'is_all_tab' => $is_all_tab,
  'need_pagination' => $need_pagination,
  'prev_page_link' => $prev_page_link,
  'next_page_link' => $next_page_link,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: популярное',
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'page' => 'popular',
  'unread_messages' => db_get_unread_message_count($con, $_SESSION['user']['id']),
]);

print($layout_content);
