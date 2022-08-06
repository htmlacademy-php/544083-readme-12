<?php
require_once('session.php');
require_once('init.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('validator.php');

$con = $con ?? null;

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
  'unread_messages' => db_get_unread_message_count($con, $_SESSION['user']['id']),
]);

print($layout_content);
