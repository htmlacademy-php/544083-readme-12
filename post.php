<?php
require_once('helpers.php');

date_default_timezone_set('Europe/Moscow');

$con = mysqli_connect("localhost", "root", "","readme");

if ($con === false) {
  print("Ошибка подключения: " . mysqli_connect_error());
  die;
}

mysqli_set_charset($con, "utf8");

$id = $_GET['id'] ?? '';

$sql_post =
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
      (SELECT COUNT(post_id) FROM likes WHERE post_id = ?) as likes,
      (SELECT COUNT(post_id) FROM comments WHERE post_id = ?) as comments
    FROM posts p
    JOIN post_types pt ON pt.id = p.type_id
    WHERE p.id = ?
  ";

$stmt_post = db_get_prepare_stmt($con, $sql_post, array_fill(0, 3, $id));
$post = db_get_fetch_all($con, $stmt_post)[0] ?? false;

include_not_found_page(boolval($post));

$post_author_id =  $post['author_id'] ?? '';

$sql_user =
  "
    SELECT
      u.login,
      u.dt_add,
      u.avatar,
      u.login,
      (SELECT COUNT(follower_id) FROM subscriptions WHERE following_id = $post_author_id) as followers_count,
      (SELECT COUNT(id) FROM posts WHERE author_id = $post_author_id) as posts_count
    FROM users u
    WHERE u.id = $post_author_id
  ";

$stmt_user = db_get_prepare_stmt($con, $sql_user);
$user = db_get_fetch_all($con, $stmt_user)[0] ?? false;

include_not_found_page(boolval($user));

$page_content = include_template('post-details.php', [
  'post' => $post,
  'user' => $user,
]);

$layout_content = include_template('layout.php', [
  'title' => 'readme Пост',
  'content' => $page_content,
]);

print($layout_content);