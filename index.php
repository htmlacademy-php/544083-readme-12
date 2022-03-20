<?php

require_once('helpers.php');

date_default_timezone_set('Europe/Moscow');

$is_auth = rand(0, 1);

$user_name = 'Alexandr';

$con = mysqli_connect("localhost", "root", "","readme");

if ($con === false) {
  print("Ошибка подключения: " . mysqli_connect_error());
  die;
} else {
  mysqli_set_charset($con, "utf8");

  $sql_post_types = "SELECT * FROM post_types";
  $stmt_post_types = db_get_prepare_stmt($con, $sql_post_types);
  mysqli_stmt_execute($stmt_post_types);
  $result_post_types = mysqli_stmt_get_result($stmt_post_types);
  $post_types = mysqli_fetch_all($result_post_types, MYSQLI_ASSOC);

  $all_tab = 'all';
  $tab = $_GET['tab'] ?? $all_tab;
  $is_all_tab = $tab === $all_tab;
  $sql_filter_post_type = !$is_all_tab ? "WHERE pt.id = ?" : '';

  $sort = mysqli_real_escape_string($con,$_GET['sort'] ?? 'views');

  $sql_posts =
    "
      SELECT
        p.id,
        p.dt_add,
        p.title, 
        p.text, 
        p.quote_author, 
        p.link, 
        p.image, 
        pt.type, 
        u.login AS author,
        u.avatar, 
        COUNT(l.post_id) as likes 
      FROM posts p
      JOIN users u ON p.author_id = u.id
      JOIN post_types pt ON pt.id = p.type_id
      LEFT JOIN likes l ON l.post_id = p.id
      $sql_filter_post_type
      GROUP BY p.id
      ORDER BY $sort DESC
    ";

  $stmt_posts = db_get_prepare_stmt($con, $sql_posts, !$is_all_tab ? [$tab] : []);
  mysqli_stmt_execute($stmt_posts);
  $result_posts = mysqli_stmt_get_result($stmt_posts);
  $posts = mysqli_fetch_all($result_posts, MYSQLI_ASSOC);
}

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
