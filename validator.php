<?php

/**
 * Валидирует текстовое поле
 *
 * @param $name
 * @param string $label
 * @param bool $is_required
 * @param int $min
 * @param int $max
 * @return array | false
 */
function validate_text_field ($name, string $label = '', bool $is_required = false, int $min = 0, int $max = 0)
{
  $field = $_POST[$name] ?? false;
  if ($is_required && empty($field)) {
    return [
      'label' => $label,
      'error' => 'Это поле должно быть заполнено'
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

  return false;
};

/**
 * Валидирует поле с хеш тегами
 *
 * @param $name
 * @param string $label
 * @return array | false
 */
function validate_hash_tags ($name, string $label) {
  $field = $_POST[$name];
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

  return false;
};

/**
 * Валидирует поле загрузки изображения
 *
 * @param $name
 * @param string $label
 * @return array | false
 */
function validate_photo($name, string $label)
{
  $file = $_FILES[$name];
  if (!empty($file)) {
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

    if (file_exists(__DIR__ . '/img/' . $file['name'])) {
      return [
        'error' => 'Изображение с таким названием уже существует, измените название или загрузите другую картинку',
        'label' => $label
      ];
    }
  }

  return false;
}

/**
 * Валидирует поле с ссылкой на изображние
 *
 * @param $name
 * @param string $label
 * @return array | false
 */
function validate_photo_link($name, string $label)
{
  $field = $_POST[$name];

  if (!empty($field)) {
    if (!filter_var($field, FILTER_VALIDATE_URL)) {
      return [
        'error' => 'Значение не является ссылкой',
        'label' => $label,
      ];
    }

    if (!in_array(pathinfo($field, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
      return [
        'error' => 'Тип файла должен соответствовать одному из форматов "png", "jpeg", "gif"',
        'label' => $label,
      ];
    }

    if (file_exists(__DIR__ . '/uploads/' . basename($field))) {
      return [
        'error' => 'Изображение с таким названием уже существует, измените название или загрузите другую картинку',
        'label' => $label
      ];
    }
  }

  return false;
}

/**
 * Валидирует поле с ссылкой на видео
 *
 * @param $name
 * @param string $label
 * @return array | false
 */
function validate_video_link($name, string $label)
{
  $field = $_POST[$name];

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

  return false;
}

/**
 * Возвращиет ошибки формы добавление пост
 *
 * @return array
 */
function get_errors_post_form (): array
{
  $errors = [];

  $rules = [
    'post-title' => function() {
      return validate_text_field('post-title', 'Заголовок', true, 0, 128);
    },
    'cite-text' => function() {
      return validate_text_field('cite-text', 'Текст цитаты', true, 0, 70);
    },
    'post-text' => function() {
      return validate_text_field('post-text', 'Текст поста', true);
    },
    'quote-author' => function() {
      return validate_text_field('quote-author', 'Автор', true, 0, 50);
    },
    'hash-tags' => function() {
      return validate_hash_tags('hash-tags', 'Теги');
    },
    'video-url' => function() {
      return validate_video_link('video-url', 'Ссылка YOUTUBE');
    },
  ];

  foreach ($_POST as $key => $field) {
    if (isset($rules[$key])) {
      $errors[$key] = $rules[$key]();
    }
  }

  $errors = array_filter($errors);

  if ($_POST['type'] === 'photo') {
    if (isset($_FILES['post-photo']) && boolval($_FILES['post-photo']['name'])) {
      $error = validate_photo('post-photo', 'Фото');
      if ($error !== false) {
        $errors['post-photo'] = $error;
      } elseif(count($errors) === 0) {
        move_uploaded_file($_FILES['post-photo']['tmp_name'], __DIR__ . '/img/' . $_FILES['post-photo']['name']);
      }
    } elseif (!empty($_POST['photo-url'])) {
      $error = validate_photo_link('photo-url', 'Ссылка из интернета');
      if ($error !== false) {
        $errors['photo-url'] = $error;
      } else {
        $field = $_POST['photo-url'];
        $downloadFile = file_get_contents($field);
        if (!$downloadFile) {
          $errors['photo-url'] = [
            'error' => 'Не удалось скачать файл',
            'label' => 'Ссылка',
          ];
        } elseif(count($errors) === 0) {
          file_put_contents(__DIR__ . '/img/' . basename($field), $downloadFile);
        }
      }
    } else {
      $errors['empty-photo'] = [
        'error' => 'Нужно загрузить или указать ссылку на фотографию',
      ];
    }
  }

  return $errors;
}