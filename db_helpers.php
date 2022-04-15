<?php
/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt | false (mysqli_stmt - Подготовленное выражение)
 */
function db_get_prepare_stmt(mysqli $link, string $sql, array $data = [], $isDebugging = false)
{
  $stmt = mysqli_prepare($link, $sql);

  if ($stmt === false) {
    if ($isDebugging) {
      $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
      die($errorMsg);
    }

    return false;
  }

  if ($data) {
    $types = '';
    $stmt_data = [];

    foreach ($data as $value) {
      $type = 's';

      if (is_int($value)) {
        $type = 'i';
      } else {
        if (is_string($value)) {
          $type = 's';
        } else {
          if (is_double($value)) {
            $type = 'd';
          }
        }
      }

      if ($type) {
        $types .= $type;
        $stmt_data[] = $value;
      }
    }

    $values = array_merge([$stmt, $types], $stmt_data);

    $func = 'mysqli_stmt_bind_param';
    $func(...$values);

    if (mysqli_errno($link) > 0) {
      if ($isDebugging) {
        $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
        die($errorMsg);
      }

      return false;
    }
  }

  return $stmt;
}

/**
 * Выбирает все строки из результирующего набора и помещает их в ассоциативный массив, обычный массив или в оба
 *
 * @param $link mysqli Ресурс соединения
 * @param $stmt mysqli_stmt Подготовленное выражение
 * @param $mode int
 * @param $isDebugging bool
 *
 * @return array | boolean
 */
function db_get_fetch_all(mysqli $link, mysqli_stmt $stmt, int $mode = MYSQLI_ASSOC, bool $isDebugging = false)
{
  if ($stmt === false) {
    if ($isDebugging) {
      $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
      die($errorMsg);
    }

    return false;
  }

  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  return mysqli_fetch_all($result, $mode);
}

/**
 * Возвращает список типо постов
 *
 * @param $link mysqli Ресурс соединения
 *
 * @return array | boolean
 */
function db_get_post_types(mysqli $link)
{
  $sql = "SELECT * FROM post_types";
  $stmt = db_get_prepare_stmt($link, $sql);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает список постов
 *
 * @param $link mysqli Ресурс соединения
 * @param $tab int | string
 * @param $is_all_tab boolean
 * @param $sort string
 *
 * @return array | boolean
 */
function db_get_posts(mysqli $link, $tab, bool $is_all_tab, string $sort)
{
  $sql_filter = !$is_all_tab ? "WHERE pt.id = ?" : '';
  $sort = mysqli_real_escape_string($link, $sort);
  $sql =
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
    $sql_filter
    GROUP BY p.id
    ORDER BY $sort DESC
  ";
  $stmt = db_get_prepare_stmt($link, $sql, !$is_all_tab ? [$tab] : []);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает пост
 *
 * @param $link mysqli Ресурс соединения
 * @param $id int
 *
 * @return array | boolean
 */
function db_get_post(mysqli $link, int $id)
{
  $sql =
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
      (SELECT COUNT(post_id) FROM likes WHERE post_id = ?) as likes_count,
      (SELECT COUNT(post_id) FROM comments WHERE post_id = ?) as comments_count
    FROM posts p
    JOIN post_types pt ON pt.id = p.type_id
    WHERE p.id = ?
  ";
  $stmt = db_get_prepare_stmt($link, $sql, array_fill(0, 3, $id));

  return db_get_fetch_all($link, $stmt)[0] ?? false;
}

/**
 * Возвращает автора поста
 *
 * @param $link mysqli Ресурс соединения
 * @param $id int
 *
 * @return array | boolean
 */
function db_get_user (mysqli $link, int $id)
{
  $sql =
  "
    SELECT
      u.login,
      u.dt_add,
      u.avatar,
      u.login,
      (SELECT COUNT(follower_id) FROM subscriptions WHERE following_id = $id) as followers_count,
      (SELECT COUNT(id) FROM posts WHERE author_id = $id) as posts_count
    FROM users u
    WHERE u.id = $id
  ";
  $stmt = db_get_prepare_stmt($link, $sql);

  return db_get_fetch_all($link, $stmt)[0] ?? false;
}

/**
 * Добавляет пост, возвращает id поста
 *
 * @param $link mysqli Ресурс соединения
 * @param $post_type_id int
 *
 * @return int
 */
function db_add_post (mysqli $link, int $post_type_id): int
{
  $content = '';
  $columns = ['author_id', 'type_id', 'title'];
  switch ($_POST['type']) {
    case 'text':
      array_push($columns, 'text');
      $content = sprintf("'%s'", mysqli_real_escape_string($link, $_POST['post-text']));
      break;
    case 'quote':
      array_push($columns, 'text', 'quote_author');
      $content = sprintf("'%s', '%s'", mysqli_real_escape_string($link, $_POST['cite-text']), mysqli_real_escape_string($link, $_POST['quote-author']));
      break;
    case 'photo':
      array_push($columns, 'image');
      if (isset($_FILES['post-photo']) && boolval($_FILES['post-photo']['name'])) {
        $content = sprintf("'%s'", $_FILES['post-photo']['name']);
      } else {
        $content = sprintf("'%s'", basename($_POST['photo-url']));
      }
      break;
    case 'link':
      array_push($columns, 'link');
      $content = sprintf("'%s'", $_POST['post-link']);
      break;
    case 'video':
      array_push($columns, 'video');
      $content = sprintf("'%s'", $_POST['video-url']);
      break;
  }
  $columns = implode(',', $columns);
  $title = sprintf("'%s'", mysqli_real_escape_string($link, $_POST['post-title']));
  $sql = "INSERT INTO posts ($columns) VALUES (1, $post_type_id, $title, $content)";
  $result = mysqli_query($link, $sql);
  include_not_found_page(boolval($result));

  $post_id = mysqli_insert_id($link);

  if (!empty($_POST['hash-tags'])) {
    $tags = explode(' ', $_POST['hash-tags']);
    foreach ($tags as $tag) {
      $tag = mysqli_real_escape_string($link, $tag);
      $sql_ht = "REPLACE INTO hashtags (name) VALUES ('$tag')";
      $result_ht = mysqli_query($link, $sql_ht);
      include_not_found_page(boolval($result_ht));
      $id_ht = mysqli_insert_id($link);

      $sql_post_by_ht = "INSERT INTO posts_by_hashtags (post_id, hash_tag_id) VALUES ($post_id, $id_ht)";
      $result_post_by_ht = mysqli_query($link, $sql_post_by_ht);
      include_not_found_page(boolval($result_post_by_ht));
    }
  }

  return $post_id;
}