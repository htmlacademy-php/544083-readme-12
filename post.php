<?php
require_once('helpers.php');

date_default_timezone_set('Europe/Moscow');

$con = mysqli_connect("localhost", "root", "","readme");

if ($con === false) {
  print("Ошибка подключения: " . mysqli_connect_error());
  die;
} else {
  mysqli_set_charset($con, "utf8");

  $id = $_GET['id'];

  $sql_posts =
    "
      SELECT 
        p.id,
        p.title,
        p.text,
        p.quote_author,
        p.link,
        p.image,
        p.views,
        p.author_id,
        pt.type,
        (SELECT COUNT(post_id) FROM likes WHERE post_id = $id) as likes,
        (SELECT COUNT(post_id) FROM comments WHERE post_id = $id) as comments
      FROM posts p
      JOIN post_types pt ON pt.id = p.type_id
      WHERE p.id = ?
    ";

  $stmt_post = db_get_prepare_stmt($con, $sql_posts, [$id]);
  mysqli_stmt_execute($stmt_post);
  $result_post = mysqli_stmt_get_result($stmt_post);
  $post = mysqli_fetch_all($result_post, MYSQLI_ASSOC)[0];

  $post_author_id =  $post['author_id'] ?? '';

  $sql_user = "
    SELECT
      u.login,
      u.dt_add,
      u.avatar,
      u.login,
      (SELECT COUNT(follower_id) FROM subscriptions WHERE following_id = $post_author_id) as followers,
      (SELECT COUNT(id) FROM posts WHERE author_id = $post_author_id) as posts
    FROM users u
    WHERE u.id = $post_author_id
 ";

  $stmt_user = db_get_prepare_stmt($con, $sql_user);
  mysqli_stmt_execute($stmt_user);
  $result_user = mysqli_stmt_get_result($stmt_user);
  $user = mysqli_fetch_all($result_user, MYSQLI_ASSOC)[0];

  $result_user = mysqli_query($con, $sql_user);
}

$page_content = include_template('post-details.php', [
  'post' => $post,
  'user' => $user,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme Пост',
  'content' => $page_content,
]);

print($layout_content);