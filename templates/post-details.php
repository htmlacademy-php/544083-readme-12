<?php
$post = $post ?? [];
$user = $user ?? [];
$current_user = $current_user ?? [];
$isFollowing = $isFollowing ?? null;
$current_time = time();
?>

<main class="page__main page__main--publication">
  <div class="container">
    <h1 class="page__title page__title--publication">
      <?= $post['title'] ?? ''?>
    </h1>
    <section class="post-details">
      <h2 class="visually-hidden">Публикация</h2>
      <div class="post-details__wrapper post-<?= $post['type'] ?? '' ?>">
        <div class="post-details__main-block post post--details">
          <?php if(!empty($post['type'])): ?>
            <?php if ($post['type'] === 'quote'): ?>
              <div class="post-details__image-wrapper post-quote">
                <div class="post__main">
                  <blockquote>
                    <p>
                      <?= $post['text'] ?? '' ?>
                    </p>
                    <cite>
                      <?= $post['quote_author'] ?? '' ?>
                    </cite>
                  </blockquote>
                </div>
              </div>
            <?php elseif ($post['type'] === 'text'): ?>
              <div class="post-details__image-wrapper post-text">
                <div class="post__main">
                  <p>
                    <?= $post['text'] ?? '' ?>
                  </p>
                </div>
              </div>
            <?php elseif ($post['type'] === 'link'): ?>
              <div class="post__main">
                <div class="post-link__wrapper">
                  <a class="post-link__external" href="http://<?= $post['link'] ?>" title="Перейти по ссылке">
                    <div class="post-link__info-wrapper">
                      <div class="post-link__icon-wrapper">
                        <img src="https://www.google.com/s2/favicons?domain=<?= $post['link'] ?>" alt="Иконка">
                      </div>
                      <div class="post-link__info">
                        <h3><?= $post['title'] ?? '' ?></h3>
                      </div>
                    </div>
                  </a>
                </div>
              </div>
            <?php elseif ($post['type'] === 'video'): ?>
              <div class="post-details__image-wrapper post-photo__image-wrapper">
                <?= embed_youtube_video($post['video'] ?? ''); ?>
              </div>
            <?php elseif ($post['type'] === 'photo'): ?>
              <div class="post-details__image-wrapper post-photo__image-wrapper">
                <img src="img/<?= $post['image'] ?? '' ?>" alt="Фото от пользователя" width="760" height="507">
              </div>
            <?php endif; ?>
          <?php endif; ?>
          <div class="post__indicators">
            <div class="post__buttons">
              <a
                class="post__indicator post__indicator--likes button"
                href="/like.php?post=<?= $post['id'] ?? '' ?>"
                title="Лайк"
              >
                <svg class="post__indicator-icon" width="20" height="17">
                  <use xlink:href="#icon-heart"></use>
                </svg>
                <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                  <use xlink:href="#icon-heart-active"></use>
                </svg>
                <span>
                  <?= $post['likes_count'] ?? '' ?>
                </span>
                <span class="visually-hidden">количество лайков</span>
              </a>
              <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                <svg class="post__indicator-icon" width="19" height="17">
                  <use xlink:href="#icon-comment"></use>
                </svg>
                <span>
                  <?= count($post['comments'] ?? []) ?>
                </span>
                <span class="visually-hidden">количество комментариев</span>
              </a>
              <a class="post__indicator post__indicator--repost button" href="#" title="Репост">
                <svg class="post__indicator-icon" width="19" height="17">
                  <use xlink:href="#icon-repost"></use>
                </svg>
                <span>5</span>
                <span class="visually-hidden">количество репостов</span>
              </a>
            </div>
            <span class="post__view">
              <?php
                $views = $post['views'] ?? 0;
                echo $views . ' ' . get_noun_plural_form($views, 'просмотр', 'просмотра', 'просмотров');
              ?>
            </span>
          </div>
          <?php if (isset($post['hash_tags'])): ?>
            <ul class="post__tags">
              <?php foreach($post['hash_tags'] as $tag): ?>
                <li>
                  <a href="<?= '/search.php?search=' . urlencode("#$tag")  ?>">
                    <?= "#$tag" ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <div class="comments">
            <form class="comments__form form" action="/comment-add.php" method="post">
              <div class="comments__my-avatar">
                <img class="comments__picture" src="img/<?= $current_user['avatar'] ?>" alt="Аватар пользователя">
              </div>
              <input type="hidden" name="post-id" value="<?= $post['id'] ?? '' ?>">
              <input type="hidden" name="user-id" value="<?= $current_user['id'] ?>">
              <input type="hidden" name="author-id" value="<?= $post['author_id'] ?>">
              <textarea name="comment-content" class="comments__textarea form__textarea" placeholder="Ваш комментарий"></textarea>
              <label class="visually-hidden">Ваш комментарий</label>
              <button class="comments__submit button button--green" type="submit">Отправить</button>
            </form>
            <?php if(count($post['comments']) > 0): ?>
              <div class="comments__list-wrapper">
                <ul class="comments__list">
                  <?php foreach($post['comments'] as $comment): ?>
                    <?php
                    $author_id = $comment['author_id'] ?? '';
                    $author_name = $comment['author_name'] ?? '';
                    $avatar = $comment['avatar'] ?? '';
                    $dt_add = $comment['dt_add'] ?? '';
                    $content = htmlspecialchars($comment['content'] ?? '');
                    ?>
                    <li class="comments__item user">
                      <div class="comments__avatar">
                        <a class="user__avatar-link" href="/profile.php?id=<?= $author_id ?>">
                          <img class="comments__picture" src="img/<?= $avatar ?>" alt="Аватар пользователя">
                        </a>
                      </div>
                      <div class="comments__info">
                        <div class="comments__name-wrapper">
                          <a class="comments__user-name" href="/profile.php?id=<?= $author_id ?>">
                            <span><?= $author_name ?></span>
                          </a>
                          <time
                            class="comments__time"
                            datetime="<?= strtotime($dt_add) ?>"
                            title="<?= date_format(date_create($dt_add), 'd.m.Y H:m'); ?>"
                          >
                            <?= relative_time($current_time - strtotime($dt_add)); ?> назад
                          </time>
                        </div>
                        <p class="comments__text">
                          <?= $content ?>
                        </p>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="post-details__user user">
          <div class="post-details__user-info user__info">
            <div class="post-details__avatar user__avatar">
              <a class="post-details__avatar-link user__avatar-link" href="#">
                <img class="post-details__picture user__picture" src="img/<?= $user['avatar'] ?? '' ?>" alt="Аватар пользователя">
              </a>
            </div>
            <div class="post-details__name-wrapper user__name-wrapper">
              <a class="post-details__name user__name" href="#">
                <span>
                  <?= $user['login'] ?? '' ?>
                </span>
              </a>
              <time
                class="post-details__time user__time"
                datetime="<?= $user['dt_add'] ?>"
              >
                <?= relative_time(time() - strtotime($user['dt_add'])) ?> на сайте
              </time>
            </div>
          </div>
          <div class="post-details__rating user__rating">
            <p class="post-details__rating-item user__rating-item user__rating-item--subscribers">
              <?php $followers_count = $user['followers_count'] ?? 0 ?>
              <span class="post-details__rating-amount user__rating-amount">
                <?= $followers_count ?>
              </span>
              <span class="post-details__rating-text user__rating-text">
                <?= get_noun_plural_form($followers_count, 'подписчик', 'подписчика', 'подписчиков') ?>
              </span>
            </p>
            <p class="post-details__rating-item user__rating-item user__rating-item--publications">
              <?php $posts_count = $user['posts_count'] ?? 0 ?>
              <span class="post-details__rating-amount user__rating-amount">
                <?= $user['posts_count'] ?>
              </span>
              <span class="post-details__rating-text user__rating-text">
                <?= get_noun_plural_form($posts_count, 'публикация', 'публикации', 'публикаций') ?>
              </span>
            </p>
          </div>
          <div class="post-details__user-buttons user__buttons">
            <a
              href="/subscription.php?id=<?= $user['id'] ?>"
              class="user__button user__button--subscription button button--main"
            >
              <?= $isFollowing ? 'Отписаться' : 'Подписаться' ?>
            </a>
            <a class="user__button user__button--writing button button--green" href="#">Сообщение</a>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>
