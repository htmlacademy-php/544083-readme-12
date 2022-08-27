<?php
$is_not_found = $is_not_found ?? false;

if ($is_not_found) {
  echo 'Страница не найдена';
} else {
  echo 'Ошибка на сервере 500';
}