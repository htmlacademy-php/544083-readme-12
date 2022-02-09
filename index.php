<?php

require_once('helpers.php');

date_default_timezone_set('Europe/Moscow');

$is_auth = rand(0, 1);

$user_name = 'Alexandr';

$con = mysqli_connect("localhost", "root", "","readme");

if ($con === false) {
  print("Ошибка подключения: " . mysqli_connect_error());
  die;
}
else {
  mysqli_set_charset($con, "utf8");

  $sql_post_types = "SELECT * FROM post_types";
  $result_post_types = mysqli_query($con, $sql_post_types);

  if (!$result_post_types) {
    print("Ошибка MySQL: " . mysqli_error($con));
    die;
  } else {
    $post_types = mysqli_fetch_all($result_post_types, MYSQLI_ASSOC);
  }

  $sql_posts =
    "
      SELECT p.title, p.text, p.quote_author, p.link, p.image, pt.class_name, pt.name AS type, u.login AS author, u.avatar FROM posts p
      JOIN users u ON p.author_id = u.id
      JOIN post_types pt ON pt.id = p.type_id
      ORDER BY views DESC
      LIMIT 6
    ";

  $result_posts = mysqli_query($con, $sql_posts);

  if (!$result_posts) {
    print("Ошибка MySQL: " . mysqli_error($con));
    die;
  } else {
    $popular_posts = mysqli_fetch_all($result_posts, MYSQLI_ASSOC);
  }
}

$page_content = include_template('main.php', [
  'title' => 'readme: популярное',
  'post_types' => $post_types,
  'popular_posts' => $popular_posts,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme: популярное',
  'is_auth' => $is_auth,
  'user_name' => $user_name,
  'content' => $page_content,
]);

print($layout_content);
