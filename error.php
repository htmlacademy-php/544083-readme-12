<?php
require_once('session.php');
require_once ('config.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');

$con = $con ?? null;

$is_server_error = isset($_GET['type']) && $_GET['type'] == 500;

$page_content = include_template($is_server_error ? 'error/server-error.php' : 'error/not-found.php');

$layout_content = include_template('layout.php', [
  'title' => 'readme: популярное',
  'content' => $page_content,
  'user' => $_SESSION['user'],
  'page' => 'error',
  'unread_messages' => db_get_unread_message_count($con, $_SESSION['user']['id']),
]);

print($layout_content);