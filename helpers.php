<?php
/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function is_date_valid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form(int $number, string $one, string $two, string $many): string
{
    $number = (int)$number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = [])
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Функция проверяет доступно ли видео по ссылке на youtube
 * @param string $url ссылка на видео
 *
 * @return string Ошибку если валидация не прошла
 */
function check_youtube_url($url)
{
    $id = extract_youtube_id($url);

    set_error_handler(function () {}, E_WARNING);
    $headers = get_headers('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $id);
    restore_error_handler();

    if (!is_array($headers)) {
        return "Видео по такой ссылке не найдено. Проверьте ссылку на видео";
    }

    $err_flag = strpos($headers[0], '200') ? 200 : 404;

    if ($err_flag !== 200) {
        return "Видео по такой ссылке не найдено. Проверьте ссылку на видео";
    }

    return true;
}

/**
 * Возвращает код iframe для вставки youtube видео на страницу
 * @param string $youtube_url Ссылка на youtube видео
 * @return string
 */
function embed_youtube_video($youtube_url)
{
    $res = "";
    $id = extract_youtube_id($youtube_url);

    if ($id) {
        $src = "https://www.youtube.com/embed/" . $id;
        $res = '<iframe width="760" height="400" src="' . $src . '" frameborder="0"></iframe>';
    }

    return $res;
}

/**
 * Возвращает img-тег с обложкой видео для вставки на страницу
 * @param string $youtube_url Ссылка на youtube видео
 * @return string
 */
function embed_youtube_cover($youtube_url)
{
    $res = "";
    $id = extract_youtube_id($youtube_url);

    if ($id) {
        $src = sprintf("https://img.youtube.com/vi/%s/mqdefault.jpg", $id);
        $res = '<img alt="youtube cover" width="320" height="120" src="' . $src . '" />';
    }

    return $res;
}

/**
 * Извлекает из ссылки на youtube видео его уникальный ID
 * @param string $youtube_url Ссылка на youtube видео
 * @return array
 */
function extract_youtube_id($youtube_url)
{
    $id = false;

    $parts = parse_url($youtube_url);

    if ($parts) {
        if ($parts['path'] ?? '' == '/watch') {
            parse_str($parts['query'] ?? '', $vars);
            $id = $vars['v'] ?? null;
        } else {
            if ($parts['host'] ?? '' == 'youtu.be') {
                $id = substr($parts['path'], 1);
            }
        }
    }

    return $id;
}

/**
 * @param $index
 * @return false|string
 */
function generate_random_date($index)
{
    $deltas = [['minutes' => 59], ['hours' => 23], ['days' => 6], ['weeks' => 4], ['months' => 11]];
    $dcnt = count($deltas);

    if ($index < 0) {
        $index = 0;
    }

    if ($index >= $dcnt) {
        $index = $dcnt - 1;
    }

    $delta = $deltas[$index];
    $timeval = rand(1, current($delta));
    $timename = key($delta);

    $ts = strtotime("$timeval $timename ago");
    $dt = date('Y-m-d H:i:s', $ts);

    return $dt;
}

/**
 * @param string $str
 * @param int $limit
 * @param string $subStr
 * @return array
 */
function cropping_text(string $str, int $limit = 300, string $subStr = '...'): array
{
  $str = explode(' ', $str);
  $croppingStr = [];
  $isLimited = true;

  if (strlen($str[0]) > $limit) {
    $croppingStr = [substr($str[0], 0, $limit)];
    $isLimited = false;
  } else {
    $strLength = 0;

    foreach ($str as $item) {
      $strLength += strlen($item);

      if ($strLength <= $limit) {
        array_push($croppingStr, $item);
      } else {
        $isLimited = false;
        break;
      }
    };
  }

  return [
    'str' => implode(' ', $croppingStr) . ($isLimited ? '' : $subStr),
    'isLimited' => $isLimited,
  ];
}

/**
 * @param int $time
 * @return string
 */
function relative_time(int $time): string {
  $minute = 60;
  $hour = $minute * 60;
  $day = $hour * 24;
  $week = $day * 7;
  $five_week = $week * 5;

  if ($time >= $five_week) {
    $relative_time = $time / $five_week;
    $string_one = 'месяц';
    $string_two = 'месяца';
    $string_many = 'месяцев';
  } elseif ($time >= $week && $time < $five_week) {
    $relative_time = $time / $week;
    $string_one = 'неделю';
    $string_two = 'недели';
    $string_many = 'недель';
  } elseif ($time >= $day && $time < $week) {
    $relative_time = $time / $day;
    $string_one = 'день';
    $string_two = 'дня';
    $string_many = 'дней';
  } elseif ($time >= $hour && $time < $day) {
    $relative_time = $time / $hour;
    $string_one = 'час';
    $string_two = 'часа';
    $string_many = 'часов';
  } else {
    $relative_time = $time / $minute;
    $string_one = 'минуту';
    $string_two = 'минуты';
    $string_many = 'минут';
  }

  $relative_time = floor($relative_time);

  return sprintf('%s %s', $relative_time, get_noun_plural_form($relative_time, $string_one, $string_two, $string_many));
};

/**
 * @param $isCorrect
 */
function include_not_found_page($isCorrect)
{
  if (boolval($isCorrect) === false) {
    header("location: error.php");
    die;
  }
}

/**
 * @param $isCorrect
 */
function include_server_error_page($isCorrect)
{
  if (boolval($isCorrect) === false) {
    header("location: error.php?type=500");
    die;
  }
}

/**
 * @param boolean $condition
 * @param string $class
 *
 * @return string
 */
function add_class (bool $condition, string $class): string
{
  return $condition ? " $class" : '';
}

/**
 * @param array $file
 * @param string $dir
 *
 * @return ?string
 */
function move_download_file(array $file, string $dir = 'img'): ?string
{
  if (!empty($file) && boolval($file['name'])) {
    $type = pathinfo($file['name'], PATHINFO_EXTENSION);
    $base_dir = __DIR__;

    do {
      $name = md5(microtime() . rand(0, 9999));
      $new_name = "$base_dir/$dir/$name.$type";
    } while (file_exists($new_name));

    $move = move_uploaded_file($file['tmp_name'], $new_name);

    if ($move) {
      return "$name.$type";
    } else {
      return '';
    }
  }

  return null;
}

/**
 * @param string $url
 * @param string $dir
 *
 * @return ?string
 */
function put_link_file(string $url, string $dir = 'img'): ?string
{
  if (!empty($url)) {
    $type = pathinfo($url, PATHINFO_EXTENSION);
    $base_dir = __DIR__;

    do {
      $name = md5(microtime() . rand(0, 9999));
      $new_name = "$base_dir/$dir/$name.$type";
    } while (file_exists($new_name));

    $downloadFile = file_get_contents($url);

    if ($downloadFile) {
      $put_file = file_put_contents($new_name, $downloadFile);

      if ($put_file) {
        return "$name.$type";
      } else {
        return '';
      }
    }
  }

  return null;
}

/**
 * @param string $date
 *
 * @return string
 */
function get_message_date (string $date): string
{
  if (!$date) {
    return '';
  }

  $month = [1 => 'янв', 'фев', 'мар', 'апр', 'мая', 'июня', 'июля', 'авг', 'сен', 'окт', 'ноя', 'дек'];
  $date_format = 'Y-m-d H:i:s';
  $current_date = date_create_from_format($date_format, date($date_format));
  $date = date_create_from_format($date_format, $date);
  $date_diff = date_diff($current_date, $date);

  if ($date_diff->y > 0) {
    return date_format($date, 'd') . ' ' . $month[date_format($date, 'n')] . ' ' . date_format($date, 'Y');
  } else if ($date_diff->d > 0) {
    return date_format($date, 'd') . ' ' .  $month[date_format($date, 'n')];
  }

  return date_format($date, 'H:i');
}
