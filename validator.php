<?php

/**
 * Валидирует текстовое поле
 *
 * @param string $field
 * @param string $label
 * @param bool $is_required
 * @param int $min
 * @param int $max
 * @return ?array
 */
function validate_text_field (string $field, string $label = '', bool $is_required = false, int $min = 0, int $max = 0): ?array
{
  if ($is_required && empty($field)) {
    return [
      'label' => $label,
      'error' => 'Это поле должно быть заполнено'
    ];
  }

  if (isset($field) && trim($field) === '') {
    return [
      'label' => $label,
      'error' => 'Это поле не должно быть пустой строкой'
    ];
  }

  $len = strlen($field);

  if (($min > 0 || $max > 0) && strlen($field) > 0) {
    if ($min > 0 && $max > 0 && $len > $max && $len < $min) {
      return [
        'label' => $label,
        'error' => "Длина поля должна быть от $min до $max символов"
      ];
    }

    if ($min > 0 && $len < $min) {
      return [
        'label' => $label,
        'error' => "Длина поля должна быть не меньше $min символов"
      ];
    }

    if ($max > 0 && $len > $max) {
      return [
        'label' => $label,
        'error' => "Длина поля должна быть не более $max символов"
      ];
    }
  }

  return null;
};

/**
 * Валидирует поле с хеш тегами
 *
 * @param string $field
 * @param string $label
 * @return ?array
 */
function validate_hash_tags (string $field, string $label): ?array
{
  $limit = 64;

  if (!empty($field)) {
    $tags = explode(' ', $field);
    $errors = [];

    foreach ($tags as $key => $word) {
      if (strlen($word) > $limit) {
        $errors[$key] = $word;
      }
    }

    if (count($errors) > 0) {
      $errors = implode(', ', $errors);

      return [
        'error' => "Длина тегов $errors превышает $limit символа",
        'label' => $label
      ];
    }
  }

  return null;
};

/**
 * Валидирует поле загрузки изображения
 *
 * @param array $file
 * @param string $label
 * @return ?array
 */
function validate_photo(array $file, string $label): ?array
{
  if (!empty($file) && boolval($file['name'])) {
    if (!in_array($file['type'], ['image/png', 'image/jpeg', 'image/gif'])) {
      return [
        'error' => 'Тип файла должен соответствовать одному из форматов "png", "jpeg", "gif"',
        'label' => $label
      ];
    }

    if (in_array($file['error'], [1, 2])) {
      return [
        'error' => 'Размер файла превышает 2MB',
        'label' => $label
      ];
    }

    if ($file['error'] !== 0) {
      return [
        'error' => 'Что то пошло не так, попробуйте еще раз',
        'label' => $label
      ];
    }
  }

  return null;
}

/**
 * Валидирует поле с ссылкой на изображние
 *
 * @param string $field
 * @param string $label
 * @return ?array
 */
function validate_photo_link(string $field, string $label): ?array
{
  if (!empty($field)) {
    if (!filter_var($field, FILTER_VALIDATE_URL)) {
      return [
        'error' => 'Значение не является ссылкой',
        'label' => $label,
      ];
    }

    $hds = get_headers($field);
    if (!$hds || (str_contains($hds[0], ' 404 '))) {
      return [
        'error' => 'Ссылки не существует',
        'label' => $label,
      ];
    }

    if (!in_array(pathinfo($field, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
      return [
        'error' => 'Тип файла должен соответствовать одному из форматов "png", "jpeg", "gif"',
        'label' => $label,
      ];
    }
  }

  return null;
}

/**
 * Валидирует поле с ссылкой на видео
 *
 * @param string $field
 * @param string $label
 * @return ?array
 */
function validate_video_link(string $field, string $label): ?array
{
  if (empty($field)) {
    return [
      'error' => 'Это поле должно быть заполнено',
      'label' => $label,
    ];
  }

  if (!filter_var($field, FILTER_VALIDATE_URL)) {
    return [
      'error' => 'Значение не является ссылкой',
      'label' => $label,
    ];
  }

  if (check_youtube_url($field) !== true) {
    return [
      'error' => 'Видео не существует',
      'label' => $label,
    ];
  }

  return null;
}

/**
 * Валидирует поле с электоронной почтой
 *
 * @param string $field
 * @param string $label
 * @return ?array
 */
function validate_email_field (string $field, string $label): ?array
{
  if (empty($field)) {
    return [
      'label' => $label,
      'error' => 'Поле долно быть заполненно'
    ];
  }

  if (!filter_var($field, FILTER_VALIDATE_EMAIL)) {
    return [
      'label' => $label,
      'error' => 'Не валидный Email'
    ];
  }

  return null;
}

/**
 * Валидирует поле с повтором пароля
 *
 * @param string $main_field
 * @param string $field
 * @param string $label
 * @return ?array
 */
function validate_repeat_password (string $main_field, string $field, string $label): ?array
{
  if (empty($main_field)) {
    return null;
  }

  if (empty($field)) {
    return [
      'label' => $label,
      'error' => 'Поле долно быть заполненно'
    ];
  }

  if ($field !== $main_field) {
    return [
      'label' => $label,
      'error' => 'Пароли не совпадают'
    ];
  }

  return null;
}

/**
 * Возвращиет ошибки формы добавление поста
 * @param array $post
 * @param array $files
 *
 * @return array
 */
function get_errors_post_form (array $post, array $files): array
{
  $errors = [];

  $rules = [
    'post-title' => fn() => validate_text_field($post['post-title'], 'Заголовок', true, 0, 128),
    'cite-text' => fn() => validate_text_field($post['cite-text'], 'Текст цитаты', true, 0, 70),
    'post-text' => fn() => validate_text_field($post['post-text'], 'Текст поста', true),
    'quote-author' => fn() => validate_text_field($post['quote-author'], 'Автор', true, 0, 50),
    'hash-tags' => fn() => validate_hash_tags($post['hash-tags'], 'Теги'),
    'video-url' => fn() => validate_video_link($post['video-url'], 'Ссылка YOUTUBE'),
  ];

  foreach ($post as $key => $field) {
    if (isset($rules[$key])) {
      $errors[$key] = $rules[$key]();
    }
  }

  if ($post['type'] === 'photo') {
    if (isset($files['post-photo']) && boolval($files['post-photo']['name'])) {
      $error = validate_photo($files['post-photo'], 'Фото');
      if ($error !== null) {
        $errors['post-photo'] = $error;
      }
    } elseif (!empty($post['photo-url'])) {
      $error = validate_photo_link($post['photo-url'], 'Ссылка из интернета');
      if ($error !== null) {
        $errors['photo-url'] = $error;
      }
    } else {
      $errors['empty-photo'] = [
        'error' => 'Нужно загрузить или указать ссылку на фотографию',
      ];
    }
  }

  return array_filter($errors);
}

/**
 * Возвращиет ошибки формы регистрации
 * @param array $post
 * @param array $file
 *
 * @return array
 */
function get_errors_join_form (array $post, array $file): array
{
  $errors = [];

  $rules = [
    'email' => fn() => validate_email_field($post['email'], 'Электронная почта'),
    'login' => fn() => validate_text_field($post['login'], 'Логин', true, 3, 32),
    'password' => fn() => validate_text_field($post['password'], 'Пароль', true, 7, 64),
    'password-repeat' => fn() => validate_repeat_password($post['password'], $post['password-repeat'], 'Повтор пароля'),
  ];

  foreach ($post as $key => $field) {
    if (isset($rules[$key])) {
      $errors[$key] = $rules[$key]();
    }
  }

  $errors['userpic-file'] = validate_photo($file, 'Фото', 'img/avatars', 'userpic-' . $post['login']);

  return array_filter($errors);
}

/**
 * Возвращиет ошибки формы авторизации
 * @param array $post
 *
 * @return array
 */
function get_errors_login_form (array $post): array
{
  $errors = [];

  $rules = [
    'login' => fn() => validate_text_field($post['login'], 'Логин', true),
    'password' => fn() => validate_text_field($post['password'], 'Пароль', true),
  ];

  foreach ($post as $key => $field) {
    if (isset($rules[$key])) {
      $errors[$key] = $rules[$key]();
    }
  }

  return array_filter($errors);
}