<?php

require_once('helpers.php');
require_once('db_helpers.php');

date_default_timezone_set('Europe/Moscow');

$is_auth = rand(0, 1);

$user_name = 'Alexandr';

$con = mysqli_connect("localhost", "root", "","readme");

if ($con === false) {
  print("Ошибка подключения: " . mysqli_connect_error());
  die;
}

mysqli_set_charset($con, "utf8");

$post_types = db_get_post_types($con);

include_not_found_page(boolval($post_types));

$all_tab = 'all';
$tab = $_GET['tab'] ?? $all_tab;
$is_all_tab = $tab === $all_tab;

$sort = $_GET['sort'] ?? 'views';

$posts = db_get_posts($con, $tab, $is_all_tab, $sort);

include_not_found_page(boolval($posts));

$page_content = include_template('main.php', [
  'post_types' => $post_types,
  'posts' => $posts,
  'tab' => $tab,
  'sort' => $sort,
  'is_all_tab' => $is_all_tab,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: популярное',
  'is_auth' => $is_auth,
  'user_name' => $user_name,
  'content' => $page_content,
]);

print($layout_content);
