<?php
require_once('session.php');
require_once ('config.php');
require_once('helpers.php');
require_once('db_helpers.php');
require_once('init.php');
require_once('validator.php');

$con = $con ?? null;

if (isset($_POST['post-id']) && (int) $_POST['post-id']) {
  $post_id = db_post_exist($con, $_POST['post-id']);
  include_server_error_page($post_id);
}

$content = $_POST['comment-content'] ?? '';
$error = validate_text_field($content, '', true);

if ($error) {
  $get_comment_name = 'comment-error';
  $ref_url_query = parse_url("{$_SERVER['HTTP_REFERER']}", PHP_URL_QUERY);
  $ref_url_query_parse = explode('&', parse_url("{$_SERVER['HTTP_REFERER']}", PHP_URL_QUERY));
  $query_start = '?';

  if ($ref_url_query_parse && !str_starts_with($ref_url_query_parse[0], $get_comment_name)) {
    $query_start = "{$ref_url_query_parse[0]}&";
  }

  $query = http_build_query(
    [$get_comment_name => [
      'id' => $_POST['post-id'],
      'error' => $error['error'],
    ]
  ]);
  $url = str_replace($ref_url_query, '', $_SERVER['HTTP_REFERER']) . "{$query_start}$query";

  header("location: $url");
  die;
}

if (isset($_POST['user-id']) && (int) $_POST['user-id']) {
  $user_id = db_user_exist($con, $_POST['user-id']);
  include_server_error_page($user_id);
}

if (isset($_POST['author-id']) && (int) $_POST['author-id']) {
  $author_id = db_user_exist($con, $_POST['author-id']);
  include_server_error_page($author_id);
}

$add_comment = db_add_comment($con, $content, $_POST['user-id'], $_POST['post-id']);
include_server_error_page($add_comment);

header("location: profile.php?id={$_POST['author-id']}");