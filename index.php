<?php

require_once('helpers.php');

date_default_timezone_set('Europe/Moscow');

$is_auth = rand(0, 1);

$user_name = 'Alexandr';

$post_types = [
  'quote' => 'post-quote',
  'text' => 'post-text',
  'photo' => 'post-photo',
  'link' => 'post-link'
];

$popular_posts = [
  [
    'title' => 'Цитата',
    'type' => $post_types['quote'],
    'content' => 'Мы в жизни любим только раз, а после ищем лишь похожих',
    'author' => 'Лариса',
    'avatar' => 'userpic-larisa-small.jpg'
  ],
  [
    'title' => 'Игра престолов',
    'type' => $post_types['text'],
    'content' => 'Не могу дождаться начала финального сезона своего любимого сериала!',
    'author' => 'Владик',
    'avatar' => 'userpic.jpg'
  ],
  [
    'title' => 'Наконец, обработал фотки!',
    'type' => $post_types['photo'],
    'content' => 'rock-medium.jpg',
    'author' => 'Виктор',
    'avatar' => 'userpic-mark.jpg'
  ],
  [
    'title' => 'Моя мечта',
    'type' => $post_types['photo'],
    'content' => 'coast-medium.jpg',
    'author' => 'Лариса',
    'avatar' => 'userpic-larisa-small.jpg'
  ],
  [
    'title' => 'Лучшие курсы',
    'type' => $post_types['link'],
    'content' => 'www.htmlacademy.ru',
    'author' => 'Владик',
    'avatar' => 'userpic.jpg'
  ],
];

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
