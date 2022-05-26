<?php
require_once('session.php');
require_once('enums.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');

date_default_timezone_set('Europe/Moscow');

$con = db_connect();

include_server_error_page($con);

mysqli_set_charset($con, "utf8");

$query = $_GET['search'] ?? '';
$posts = [];

if ($query !== '') {
  $query = trim($query);

  if (!str_starts_with($query, '#')) {
    $posts = db_get_search_posts($con, $query);
  } else {
    $posts = db_get_hash_tag_posts($con, substr($query, 1));
  }
}

$page_content = include_template('search-results.php', [
  'query' => $query,
  'posts' => $posts,
]);

$layout_content = include_template('layout.php', [
  'title' => 'Результаты поиска',
  'content' => $page_content,
  'user' => $_SESSION['user'],
]);

print($layout_content);
