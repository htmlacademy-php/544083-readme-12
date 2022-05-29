<?php
/**
 * @param string $host_name
 * @param string $username
 * @param string $password
 * @param string $database
 * @return mysqli
 */
function db_connect(string $host_name = 'localhost', string $username = 'root', string $password = '', string $database = 'readme'): mysqli
{
  $con = mysqli_connect($host_name, $username, $password, $database);
  if ($con === false) {
    if (IS_DEBUGGING) {
      print("Ошибка подключения: " . mysqli_connect_error());
      die;
    }
  }

  return $con;
}


/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param $data array Данные для вставки на место плейсхолдеров
 *
 * @return ?mysqli_stmt (mysqli_stmt - Подготовленное выражение)
 */
function db_get_prepare_stmt(mysqli $link, string $sql, array $data = []): ?mysqli_stmt
{
  $stmt = mysqli_prepare($link, $sql);

  if ($stmt === false) {
    if (IS_DEBUGGING) {
      $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
      die($errorMsg);
    }

    return null;
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

      $types .= $type;
      $stmt_data[] = $value;
    }

    $values = array_merge([$stmt, $types], $stmt_data);
    mysqli_stmt_bind_param(...$values);

    if (mysqli_errno($link) > 0) {
      if (IS_DEBUGGING) {
        $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
        die($errorMsg);
      }

      return null;
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
 *
 * @return ?array
 */
function db_get_fetch_all(mysqli $link, mysqli_stmt $stmt, int $mode = MYSQLI_ASSOC): ?array
{
  if ($stmt === false) {
    if (IS_DEBUGGING) {
      $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
      die($errorMsg);
    }

    return null;
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
 * @return ?array
 */
function db_get_post_types(mysqli $link): ?array
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
 * @param $sort string|null
 * @param  $user_id int|null
 *
 * @return ?array
 */
function db_get_posts(mysqli $link, int|string $tab, bool $is_all_tab, string $sort = null, int $user_id = null): ?array
{
  $sql_filter = !$is_all_tab ? "WHERE pt.id = ?" : '';

  if ($user_id !== null) {
    if ($sql_filter !== '') {
      $sql_filter = $sql_filter . " AND u.id = $user_id";
    } else {
      $sql_filter = "WHERE u.id = $user_id";
    }
  }


  $sort = is_null($sort) ? '' : "ORDER BY $sort DESC";

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
      COUNT(l.post_id) as likes_count,
      COUNT(c.post_id) as comments_count
    FROM posts p
    JOIN users u ON p.author_id = u.id
    JOIN post_types pt ON pt.id = p.type_id
    LEFT JOIN likes l ON l.post_id = p.id
    LEFT JOIN comments c ON c.post_id = p.id
    $sql_filter
    GROUP BY p.id
    $sort
  ";
  $stmt = db_get_prepare_stmt($link, $sql, !$is_all_tab ? [$tab] : []);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает пост
 *
 * @param $link mysqli Ресурс соединения
 * @param $post_id int
 *
 * @return ?array
 */
function db_get_post(mysqli $link, int $post_id): ?array
{
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
      (SELECT COUNT(post_id) FROM likes WHERE post_id = ?) as likes_count,
      (SELECT COUNT(post_id) FROM comments WHERE post_id = ?) as comments_count
    FROM posts p
    JOIN post_types pt ON pt.id = p.type_id
    WHERE p.id = ?
  ";
  $stmt_post = db_get_prepare_stmt($link, $sql_post, array_fill(0, 3, $post_id));
  $post = db_get_fetch_all($link, $stmt_post)[0] ?? null;

  if ($post !== null) {
    $sql_ht =
      "
        SELECT ht.name FROM posts_by_hashtags pbh
        JOIN hashtags ht ON pbh.hash_tag_id = ht.id
        WHERE pbh.post_id = ?
      ";
    $stmt_ht = db_get_prepare_stmt($link, $sql_ht, [$post_id]);
    $hash_tags = db_get_fetch_all($link, $stmt_ht) ?? null;

    if ($hash_tags !== null) {
      $post['hash_tags'] = array_column($hash_tags, 'name');
    }
  }

  return $post;
}

/**
 * Возвращает список постов по поиску
 *
 * @param $link mysqli Ресурс соединения
 * @param $query string
 *
 * @return ?array
 */
function db_get_search_posts(mysqli $link, string $query): ?array
{
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
      COUNT(l.post_id) as likes_count,
      COUNT(c.post_id) as comments_count
    FROM posts p
    JOIN users u ON p.author_id = u.id
    JOIN post_types pt ON pt.id = p.type_id
    LEFT JOIN likes l ON l.post_id = p.id
    LEFT JOIN comments c ON c.post_id = p.id
    WHERE MATCH(p.title, p.text, p.quote_author) AGAINST(?)
    GROUP BY p.id
  ";
  $stmt = db_get_prepare_stmt($link, $sql, [$query]);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает список постов по хештегу
 *
 * @param $link mysqli Ресурс соединения
 * @param $tag string
 *
 * @return ?array
 */
function db_get_hash_tag_posts(mysqli $link, string $tag): ?array
{
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
      COUNT(l.post_id) as likes_count,
      COUNT(c.post_id) as comments_count
    FROM posts p
    JOIN users u ON p.author_id = u.id
    JOIN post_types pt ON pt.id = p.type_id
    LEFT JOIN likes l ON l.post_id = p.id
    LEFT JOIN comments c ON c.post_id = p.id
    WHERE p.id in (SELECT post_id FROM posts_by_hashtags psb WHERE (psb.hash_tag_id in (SELECT id FROM hashtags WHERE name = ?)))
    GROUP BY p.id
    ORDER BY dt_add DESC
  ";
  $stmt = db_get_prepare_stmt($link, $sql, [$tag]);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает автора поста
 *
 * @param $link mysqli Ресурс соединения
 * @param $id int
 *
 * @return ?array
 */
function db_get_user(mysqli $link, int $id): ?array
{
  $sql =
  "
    SELECT
      u.login,
      u.dt_add,
      u.avatar,
      (SELECT COUNT(follower_id) FROM subscriptions WHERE following_id = $id) as followers_count,
      (SELECT COUNT(id) FROM posts WHERE author_id = $id) as posts_count
    FROM users u
    WHERE u.id = $id
  ";
  $stmt = db_get_prepare_stmt($link, $sql);

  return db_get_fetch_all($link, $stmt)[0] ?? null;
}

/**
 * Добавляет пост, возвращает id поста
 *
 * @param $link mysqli Ресурс соединения
 * @param $post array
 * @param $post_type_id int
 * @param $author_id int
 * @param $download_img_name string
 * @param $link_img_name string
 *
 * @return ?int
 */
function db_add_post(mysqli $link, array $post, string $download_img_name, string $link_img_name, int $post_type_id, int $author_id): ?int
{
  $content = '';
  $columns = ['author_id', 'type_id', 'title'];
  switch ($post['type']) {
    case 'text':
      array_push($columns, 'text');
      $content = sprintf("'%s'", mysqli_real_escape_string($link, $post['post-text']));
      break;
    case 'quote':
      array_push($columns, 'text', 'quote_author');
      $content = sprintf("'%s', '%s'", mysqli_real_escape_string($link, $post['cite-text']), mysqli_real_escape_string($link, $post['quote-author']));
      break;
    case 'photo':
      array_push($columns, 'image');
      if (boolval($download_img_name)) {
        $content = "'$download_img_name'";
      } else {
        $content = "'$link_img_name'";
      }
      break;
    case 'link':
      array_push($columns, 'link');
      $content = sprintf("'%s'", $post['post-link']);
      break;
    case 'video':
      array_push($columns, 'video');
      $content = sprintf("'%s'", $post['video-url']);
      break;
  }
  $columns = implode(',', $columns);
  $title = sprintf("'%s'", mysqli_real_escape_string($link, $post['post-title']));
  $sql = "INSERT INTO posts ($columns) VALUES ($author_id, $post_type_id, $title, $content)";
  $result = mysqli_query($link, $sql);

  if ($result === false) {
    if (IS_DEBUGGING) {
      die(mysqli_error($link));
    }

    return null;
  }

  $post_id = mysqli_insert_id($link);

  if (!empty($post['hash-tags'])) {
    $tags = explode(' ', $post['hash-tags']);
    foreach ($tags as $tag) {
      $tag = mysqli_real_escape_string($link, $tag);

      $sql_ht_select = "SELECT id FROM hashtags WHERE name = ?";
      $stmt_ht = db_get_prepare_stmt($link, $sql_ht_select, [$tag]);
      $hashtags = db_get_fetch_all($link, $stmt_ht)[0] ?? null;
      $id_ht = null;

      if ($hashtags === null) {
        $sql_ht_insert = "INSERT INTO hashtags (name) VALUES ('$tag')";
        $result_ht = mysqli_query($link, $sql_ht_insert);

        if ($result_ht === false) {
          if (IS_DEBUGGING) {
            die(mysqli_error($link));
          }

          return null;
        }

        $id_ht = mysqli_insert_id($link);
      } else {
        $id_ht = $hashtags['id'];
      }

      $sql_post_by_ht = "INSERT INTO posts_by_hashtags (post_id, hash_tag_id) VALUES ($post_id, $id_ht)";
      $result_post_by_ht = mysqli_query($link, $sql_post_by_ht);

      if ($result_post_by_ht === false) {
        if (IS_DEBUGGING) {
          die(mysqli_error($link));
        }

        return null;
      }
    }
  }

  return $post_id;
}

/**
 * Добавляет юзера
 *
 * @param $link mysqli Ресурс соединения
 * @param $post array
 * @param $avatar string
 * @return boolean
 */
function db_add_user(mysqli $link, array $post, string $avatar): bool
{
  $email = mysqli_real_escape_string($link, $post['email']);
  $login = mysqli_real_escape_string($link, $post['login']);
  $password = password_hash($post['password'], PASSWORD_DEFAULT);
  $avatar = $avatar ?? null;
  $sql = "INSERT INTO users (email, login, password, avatar) VALUES ('$email', '$login', '$password', '$avatar')";
  $result = mysqli_query($link, $sql);

  if ($result === false) {
    if (IS_DEBUGGING) {
      die(mysqli_error($link));
    }

    return false;
  }

  return true;
};

/**
 * Возвращает юзера по логину
 *
 * @param $link mysqli Ресурс соединения
 * @param $login string
 * @return boolean
 */
function db_get_login_user (mysqli $link, string $login)
{
  $login = mysqli_real_escape_string($link, $login);
  $sql = "SELECT id, login, avatar, password FROM users WHERE login = '$login'";
  $stmt = db_get_prepare_stmt($link, $sql);

  return db_get_fetch_all($link, $stmt)[0] ?? null;
}