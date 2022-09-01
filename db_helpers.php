<?php
/**
 * @param string $host_name
 * @param string $username
 * @param string $password
 * @param string $database
 * @return mysqli
 */
function db_connect(string $host_name = HOST_NAME, string $username = DB_USERNAME, string $password = DB_PASSWORD, string $database = DB_NAME): mysqli
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

  if (!$stmt) {
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
      }

      if (is_string($value)) {
        $type = 's';
      }

      if (is_double($value)) {
        $type = 'd';
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
 * @param $tab int|string
 * @param $is_all_tab boolean
 * @param $sort ?string
 * @param $user_ids ?array
 * @param $limit ?int
 * @param $offset ?int
 * @return ?array
 */
function db_get_posts(
  mysqli $link,
  string $tab,
  bool $is_all_tab,
  string $sort = null,
  array $user_ids = null,
  int $limit = null,
  int $offset = null
): ?array {
  $sql_filter = !$is_all_tab ? "WHERE pt.id = ?" : '';

  $stmt_params = !$is_all_tab ? [$tab] : [];

  if ($user_ids) {
    $sql_filter_user = $is_all_tab ? "WHERE u.id IN (" : " AND (u.id IN (";

    $sql_filter_user .= implode(',', $user_ids);

    $sql_filter_user .= $is_all_tab ? ')' : '))';

    $sql_filter .= $sql_filter_user;
  }

  if (is_null($sort)) {
    $sort = '';
  } else {
    $sort = mysqli_real_escape_string($link, $sort);
    $sort = "ORDER BY $sort DESC";
  }

  $sql_pagination = '';
  if (is_int($limit) && is_int($offset)) {
    $sql_pagination = "LIMIT ? OFFSET ?";
    $stmt_params = array_merge($stmt_params, [$limit, $offset]);
  }

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
      p.author_id,
      pt.type,
      u.login AS author,
      u.avatar,
    (SELECT COUNT(post_id) FROM likes WHERE post_id = p.id) as likes_count
    FROM posts p
    JOIN users u ON p.author_id = u.id
    JOIN post_types pt ON pt.id = p.type_id
    $sql_filter
    GROUP BY p.id
    $sort
    $sql_pagination
  ";

  $stmt = db_get_prepare_stmt($link, $sql, $stmt_params);

  $posts = db_get_fetch_all($link, $stmt);

  if ($posts !== null) {
    foreach ($posts as $key => $post) {
      $sql_ht =
        "
        SELECT ht.name FROM posts_by_hashtags pbh
        JOIN hashtags ht ON pbh.hash_tag_id = ht.id
        WHERE pbh.post_id = ?
      ";
      $stmt_ht = db_get_prepare_stmt($link, $sql_ht, [$post['id']]);
      $hash_tags = db_get_fetch_all($link, $stmt_ht) ?? null;

      if ($hash_tags !== null) {
        $posts[$key]['hash_tags'] = array_column($hash_tags, 'name');
      }

      $sql_likes =
        "
          SELECT
            l.user_id,
            l.dt_add,
            u.avatar,
            u.login
          FROM likes l
          JOIN users u ON u.id = l.user_id
          WHERE post_id = ?
          ORDER BY l.dt_add DESC
        ";
      $stmt_likes = db_get_prepare_stmt($link, $sql_likes, [$post['id']]);
      $likes = db_get_fetch_all($link, $stmt_likes) ?? null;

      if ($likes !== null) {
        $posts[$key]['likes'] = $likes;
      }

      $sql_comments =
        "
          SELECT
            c.content,
            c.dt_add,
            u.id as author_id,
            u.login as author_name,
            u.avatar
          FROM comments c
          JOIN users u ON c.author_id = u.id
          WHERE post_id = ?
        ";
      $stmt_comments = db_get_prepare_stmt($link, $sql_comments, [$post['id']]);
      $comments = db_get_fetch_all($link, $stmt_comments) ?? null;

      if ($comments !== null) {
        $posts[$key]['comments'] = $comments;
      }
    }
  }

  return $posts;
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
      (SELECT COUNT(post_id) FROM likes WHERE post_id = ?) as likes_count
    FROM posts p
    JOIN post_types pt ON pt.id = p.type_id
    WHERE p.id = ?
  ";
  $stmt_post = db_get_prepare_stmt($link, $sql_post, [$post_id, $post_id]);
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

    $sql_comments =
      "
        SELECT
          c.content,
          c.dt_add,
          u.id as author_id,
          u.login as author_name,
          u.avatar
        FROM comments c
        JOIN users u ON c.author_id = u.id
        WHERE c.post_id = ?
      ";
    $stmt_comments = db_get_prepare_stmt($link, $sql_comments, [$post_id]);
    $comments = db_get_fetch_all($link, $stmt_comments) ?? null;

    if ($comments !== null) {
      $post['comments'] = $comments;
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
      (SELECT COUNT(post_id) FROM likes WHERE post_id = p.id) as likes_count,
      (SELECT COUNT(post_id) FROM comments WHERE post_id = p.id) as comments_count
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
      (SELECT COUNT(post_id) FROM likes WHERE post_id = p.id) as likes_count,
      (SELECT COUNT(post_id) FROM comments WHERE post_id = p.id) as comments_count
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
      u.id,
      u.login,
      u.email,
      u.dt_add,
      u.avatar,
      (SELECT COUNT(follower_id) FROM subscriptions WHERE following_id = ?) as followers_count,
      (SELECT COUNT(id) FROM posts WHERE author_id = ?) as posts_count
    FROM users u
    WHERE u.id = ?
  ";
  $stmt = db_get_prepare_stmt($link, $sql, [$id, $id, $id]);

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
 * @return bool
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
 * @return ?array
 */
function db_get_login_user (mysqli $link, string $login): ?array
{
  $login = mysqli_real_escape_string($link, $login);
  $sql = "SELECT id, login, avatar, password FROM users WHERE login = '$login'";
  $stmt = db_get_prepare_stmt($link, $sql);

  return db_get_fetch_all($link, $stmt)[0] ?? null;
}

/**
 * Проверяет поставил ли юзер like
 *
 * @param $link mysqli Ресурс соединения
 * @param $post_id int
 * @param $user_id int
 * @return bool
 */

function db_is_post_liked (mysqli $link, int $user_id, int $post_id): bool
{
  $sql = "SELECT user_id FROM likes WHERE user_id = ? AND post_id = ?";
  $stmt = db_get_prepare_stmt($link, $sql, [$user_id, $post_id]);
  $result = db_get_fetch_all($link, $stmt);

  return is_array($result) && count($result) > 0;
}

/**
 * Добавляет like
 *
 * @param $link mysqli Ресурс соединения
 * @param $post_id int
 * @param $user_id int
 * @return bool
 */
function db_add_post_like (mysqli $link, int $user_id, int $post_id): bool
{
  $sql = "INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)";
  $result = mysqli_query($link, $sql);

  if (!$result) {
    if (IS_DEBUGGING) {
      die(mysqli_error($link));
    }
  }

  return $result;
}

/**
 * Удаляет like
 *
 * @param $link mysqli Ресурс соединения
 * @param $post_id int
 * @param $user_id int
 * @return bool
 */
function db_delete_post_like (mysqli $link, int $user_id, int $post_id): bool
{
  $sql = "DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id";

  $result = mysqli_query($link, $sql);

  if (!$result && IS_DEBUGGING) {
    die(mysqli_error($link));
  }

  return $result;
}

/**
 * Добавляет просмотр посту
 *
 * @param $link mysqli Ресурс соединения
 * @param $post_id int
 * @return bool
 */
function db_add_post_view (mysqli $link, int $post_id): bool
{
  $sql = "UPDATE posts SET views = views + 1 WHERE id = $post_id";

  $result = mysqli_query($link, $sql);

  if (!$result && IS_DEBUGGING) {
    die(mysqli_error($link));
  }

  return $result;
}

/**
 * Проверяет существует ли пост
 *
 * @param $link mysqli Ресурс соединения
 * @param $post_id int
 * @return ?int
 */
function db_post_exist (mysqli $link, int $post_id): ?int
{
  $sql = "SELECT id FROM posts WHERE id = ?";
  $stmt = db_get_prepare_stmt($link, $sql, [$post_id]);
  return db_get_fetch_all($link, $stmt)[0]['id'] ?? null;
}

/**
 * Проверяет существует ли юзер
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int
 * @return ?int
 */
function db_user_exist (mysqli $link, int $user_id): ?int
{
  $sql = "SELECT id FROM users WHERE id = ?";
  $stmt = db_get_prepare_stmt($link, $sql, [$user_id]);
  return db_get_fetch_all($link, $stmt)[0]['id'] ?? null;
}

/**
 * Добавляет комментарий
 *
 * @param $link mysqli Ресурс соединения
 * @param $content string
 * @param $user_id int
 * @param $post_id int
 * @return bool
 */
function db_add_comment (mysqli $link, string $content, int $user_id, int $post_id): bool
{
  $content = trim($content);
  $sql = "INSERT INTO comments (content, author_id, post_id) VALUES ('$content', $user_id, $post_id)";
  $result = mysqli_query($link, $sql);

  if (!$result && IS_DEBUGGING) {
    die(mysqli_error($link));
  }

  return $result;
}

/**
 * Проверяет является ли один пользователь подписчиком другого
 *
 * @param $link mysqli Ресурс соединения
 * @param $following_id int
 * @param $follower_id int
 * @return ?array
 */
function db_is_following (mysqli $link, int $following_id, int $follower_id): ?array
{
  $sql = "SELECT following_id, follower_id FROM subscriptions WHERE following_id = ? AND follower_id = ?";
  $stmt = db_get_prepare_stmt($link, $sql, [$following_id, $follower_id]);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Добавляет подписчка
 *
 * @param $link mysqli Ресурс соединения
 * @param $following_id int
 * @param $follower_id int
 * @return bool
 */
function db_add_following (mysqli $link, int $following_id, int $follower_id): bool
{
  $sql = "INSERT INTO subscriptions (following_id, follower_id) VALUES ($following_id, $follower_id)";

  $result = mysqli_query($link, $sql);

  if (!$result && IS_DEBUGGING) {
    die(mysqli_error($link));
  }

  return $result;
}

/**
 * Удаляет подписчка
 *
 * @param $link mysqli Ресурс соединения
 * @param $following_id int
 * @param $follower_id int
 * @return bool
 */
function db_delete_following (mysqli $link, int $following_id, int $follower_id): bool
{
  $sql = "DELETE FROM subscriptions WHERE following_id = $following_id AND follower_id = $follower_id";

  $result = mysqli_query($link, $sql);

  if (!$result && IS_DEBUGGING) {
    die(mysqli_error($link));
  }

  return $result;
}

/**
 * Возвращает список подписок
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int
 * @return array
 */
function db_get_followings (mysqli $link, int $user_id): array
{
  $sql =
    "
        SELECT
         s.following_id as id,
         u.login,
         u.avatar,
         u.dt_add,
         (SELECT COUNT(sb.follower_id) FROM subscriptions sb WHERE sb.following_id = s.following_id) as followers_count,
         (SELECT COUNT(id) FROM posts WHERE author_id = s.following_id) as posts_count
        FROM subscriptions s
        JOIN users u ON u.id = s.following_id
        WHERE follower_id = ?
    ";

  $stmt = db_get_prepare_stmt($link, $sql, [$user_id]);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает список подписччико
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int
 * @return array
 */
function db_get_followers (mysqli $link, int $user_id): array
{
  $sql =
    "
        SELECT
         s.follower_id as id,
         u.login,
         u.email,
         u.avatar,
         u.dt_add,
         (SELECT COUNT(sb.follower_id) FROM subscriptions sb WHERE sb.following_id = s.follower_id) as followers_count,
         (SELECT COUNT(id) FROM posts WHERE author_id = s.follower_id) as posts_count
        FROM subscriptions s
        JOIN users u ON u.id = s.follower_id
        WHERE following_id = ?
    ";

  $stmt = db_get_prepare_stmt($link, $sql, [$user_id]);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает список диалогов
 *
 * @param $link mysqli Ресурс соединения
 * @param $id int
 * @return array
 */
function db_get_dialogs (mysqli $link, int $id): array
{
  $sql =
    "
      SELECT
        u.id,
        u.login,
        u.avatar,
        m.dt_add,
        m.content,
        (SELECT count(mr.is_read) FROM messages mr WHERE mr.is_read = 0 AND (mr.recipient_id = ? AND mr.sender_id = m.sender_id)) as unread_count
      FROM messages m
      JOIN users u ON (u.id IN (m.recipient_id, m.sender_id)) AND u.id != ?
      WHERE (m.sender_id = ? OR m.recipient_id = ?)
      AND m.dt_add IN 
        (SELECT MAX(ms.dt_add) FROM messages ms JOIN users us ON (us.id IN (ms.recipient_id, ms.sender_id)) AND us.id != ? GROUP BY us.id)
      ORDER BY m.dt_add DESC
    ";

  $stmt = db_get_prepare_stmt($link, $sql, [$id, $id, $id, $id, $id]);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает диалог
 *
 * @param $link mysqli Ресурс соединения
 * @param $dialog_user_id int
 * @param $current_user_id int
 * @return array
 */
function db_get_dialog (mysqli $link, int $dialog_user_id, int $current_user_id): array
{
  $sql =
    "
      SELECT
        m.recipient_id,
        m.sender_id,
        m.content,
        m.dt_add
      FROM messages m
      WHERE (m.sender_id = $dialog_user_id AND m.recipient_id = $current_user_id) OR (m.sender_id = $current_user_id AND m.recipient_id = $dialog_user_id)
    ";

  $stmt = db_get_prepare_stmt($link, $sql);

  return db_get_fetch_all($link, $stmt);
}

/**
 * Возвращает количество непрочитанных сообщений
 *
 * @param $link mysqli Ресурс соединения
 * @param $recipient_id int
 * @return int
 */
function db_get_unread_message_count (mysqli $link, int $recipient_id): int
{
  $sql = "SELECT COUNT(is_read) AS unread_count from messages WHERE is_read = 0 AND recipient_id = ?";

  $stmt = db_get_prepare_stmt($link, $sql, [$recipient_id]);

  return db_get_fetch_all($link, $stmt)[0]['unread_count'] ?? 0;
}

/**
 * Устанавливает сообщения в статус прочитанно
 *
 * @param $link mysqli Ресурс соединения
 * @param $recipient_id int
 * @param $sender_id int
 * @return bool
 */
function db_set_read_messages (mysqli $link, int $sender_id, int $recipient_id): bool
{
  $sql = "UPDATE messages SET is_read = 1 WHERE is_read = 0 AND (sender_id = $sender_id AND recipient_id = $recipient_id)";

  $result = mysqli_query($link, $sql);

  if (!$result && IS_DEBUGGING) {
    die(mysqli_error($link));
  }

  return $result;
}

/**
 * Добавляет сообщение
 *
 * @param $link mysqli Ресурс соединения
 * @param $sender_id int
 * @param $recipient_id int
 * @param $message string
 * @return boolean
 */
function db_add_message (mysqli $link, int $sender_id, int $recipient_id, string $message): bool
{
  $message = mysqli_real_escape_string($link, trim($message));
  $sql = "INSERT INTO messages (sender_id, recipient_id, content) VALUES ($sender_id, $recipient_id, '$message')";

  $result = mysqli_query($link, $sql);

  if (!$result && IS_DEBUGGING) {
    die(mysqli_error($link));
  }

  return $result;
}