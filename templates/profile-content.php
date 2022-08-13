<?php
$user = $user ?? [];
$posts = $posts ?? [];
$current_user = $current_user ?? [];
$followings = $followings ?? [];
$isFollowing = $isFollowing ?? null;
$tab = $tab ?? null;
$current_time = time();
$comment_error = $comment_error ?? null;
?>

<main class="page__main page__main--profile">
  <h1 class="visually-hidden">Профиль</h1>
  <div class="profile profile--default">
    <div class="profile__user-wrapper">
      <div class="profile__user user container">
        <div class="profile__user-info user__info">
          <div class="profile__avatar user__avatar">
            <img class="profile__picture user__picture" src="img/<?= $user['avatar'] ?? '' ?>" alt="Аватар пользователя">
          </div>
          <div class="profile__name-wrapper user__name-wrapper">
            <span class="profile__name user__name"><?= $user['login'] ?></span>
            <time class="profile__user-time user__time" datetime="<?= $user['dt_add'] ?>">
              <?= relative_time(time() - strtotime($user['dt_add'])) ?> на сайте
            </time>
          </div>
        </div>
        <div class="profile__rating user__rating">
          <p class="profile__rating-item user__rating-item user__rating-item--publications">
            <span class="user__rating-amount">
              <?= $user['posts_count'] ?? 0 ?>
            </span>
            <span class="profile__rating-text user__rating-text">
              <?= get_noun_plural_form($user['posts_count'] ?? 0, 'публикация', 'публикации', 'публикаций') ?>
            </span>
          </p>
          <p class="profile__rating-item user__rating-item user__rating-item--subscribers">
            <span class="user__rating-amount">
              <?= $user['followers_count'] ?? 0 ?>
            </span>
            <span class="profile__rating-text user__rating-text">
              <?= get_noun_plural_form($user['followers_count'] ?? 0, 'подписчик', 'подписчика', 'подписчиков') ?>
            </span>
          </p>
        </div>
        <div class="profile__user-buttons user__buttons">
          <a
            href="/subscription.php?id=<?= $user['id'] ?>"
            class="profile__user-button user__button user__button--subscription button button--main"
          >
            <?= $isFollowing ? 'Отписаться' : 'Подписаться' ?>
          </a>
          <a
            class="profile__user-button user__button user__button--writing button button--green"
            href="/messages.php?id=<?= $user['id'] ?>"
          >
            Сообщение
          </a>
        </div>
      </div>
    </div>
    <div class="profile__tabs-wrapper tabs">
      <div class="container">
        <div class="profile__tabs filters">
          <b class="profile__tabs-caption filters__caption">Показать:</b>
          <ul class="profile__tabs-list filters__list tabs__list">
            <li class="profile__tabs-item filters__item">
              <a
                href="/profile.php?id=<?= $user['id'] ?>"
                class="profile__tabs-link filters__button tabs__item tabs__item--active button<?= add_class(!$tab, 'filters__button--active') ?>"
              >
                Посты
              </a>
            </li>
            <li class="profile__tabs-item filters__item">
              <a
                class="profile__tabs-link filters__button tabs__item button<?= add_class($tab === 'likes', 'filters__button--active') ?>"
                href="/profile.php?id=<?= $user['id'] ?>&tab=likes"
              >
                Лайки
              </a>
            </li>
            <li class="profile__tabs-item filters__item">
              <a
                class="profile__tabs-link filters__button tabs__item button<?= add_class($tab === 'subscriptions', 'filters__button--active') ?>"
                href="/profile.php?id=<?= $user['id'] ?>&tab=<?= 'subscriptions' ?>"
              >
                Подписки
              </a>
            </li>
          </ul>
        </div>
        <div class="profile__tab-content">
          <?php if(!$tab): ?>
            <section class="profile__posts tabs__content tabs__content--active">
              <h2 class="visually-hidden">Публикации</h2>
              <?php foreach ($posts as $post): ?>
                <?php
                $id = $post['id'] ?? '';
                $title = htmlspecialchars($post['title']) ?? '';
                $type = $post['type'] ?? '';
                $text = htmlspecialchars($post['text']) ?? '';
                $quote_author = htmlspecialchars($post['quote_author']) ?? '';
                $link = htmlspecialchars($post['link'] ?? '');
                $image = htmlspecialchars($post['image'] ?? '');
                $avatar = htmlspecialchars($post['avatar'] ?? '');
                $author = $post['author'] ?? '';
                $author_id = $post['author_id'] ?? '';
                $id = $post['id'] ?? '';
                $likes = $post['likes'] ?? [];
                $comments= $post['comments'] ?? [];
                $dt_add = $post['dt_add'] ?? '';
                $hash_tags = $post['hash_tags'] ?? [];
                $is_comment_add_error = $comment_error && (int) $comment_error['id'] === $id && (bool)$comment_error['error'];
                ?>
                <article class="profile__post post post-<?= $type ?>">
                  <header class="post__header">
                    <h2>
                      <a href="/post.php?id=<?=$id?>">
                        <?= $title ?>
                      </a>
                    </h2>
                  </header>
                  <div class="post__main">
                    <?php if($type === 'photo'): ?>
                      <div class="post-photo__image-wrapper">
                        <img src="img/<?= $image ?>" alt="Фото от пользователя" width="760" height="396">
                      </div>
                    <?php elseif($type === 'text'): ?>
                      <?php $text = cropping_text($text); ?>
                      <p><?= $text['str'] ?></p>
                      <?php if (!$text['isLimited']):  ?>
                        <a class="post-text__more-link" href="#">Читать далее</a>
                      <?php endif; ?>
                    <?php elseif($type === 'quote'): ?>
                      <blockquote>
                        <p><?= $text ?></p>
                        <cite><?= $quote_author ?></cite>
                      </blockquote>
                    <?php elseif($type === 'video'): ?>
                      <div class="post-video__block">
                        <div class="post-video__preview">
                          <img src="img/coast.jpg" alt="Превью к видео" width="760" height="396">
                        </div>
                        <div class="post-video__control">
                          <button class="post-video__play post-video__play--paused button button--video" type="button"><span class="visually-hidden">Запустить видео</span></button>
                          <div class="post-video__scale-wrapper">
                            <div class="post-video__scale">
                              <div class="post-video__bar">
                                <div class="post-video__toggle"></div>
                              </div>
                            </div>
                          </div>
                          <button class="post-video__fullscreen post-video__fullscreen--inactive button button--video" type="button"><span class="visually-hidden">Полноэкранный режим</span></button>
                        </div>
                        <button class="post-video__play-big button" type="button">
                          <svg class="post-video__play-big-icon" width="27" height="28">
                            <use xlink:href="#icon-video-play-big"></use>
                          </svg>
                          <span class="visually-hidden">Запустить проигрыватель</span>
                        </button>
                      </div>
                    <?php elseif($type === 'link'): ?>
                      <div class="post-link__wrapper">
                        <a class="post-link__external" href="<?= $link ?>" title="Перейти по ссылке">
                          <div class="post-link__icon-wrapper">
                            <img src="https://www.google.com/s2/favicons?domain=<?= $post['link'] ?>" alt="Иконка">
                          </div>
                          <div class="post-link__info">
                            <h3><?= $title ?></h3>
                            <span><?= $post['link'] ?></span>
                          </div>
                          <svg class="post-link__arrow" width="11" height="16">
                            <use xlink:href="#icon-arrow-right-ad"></use>
                          </svg>
                        </a>
                      </div>
                    <?php endif ?>
                  </div>
                  <footer class="post__footer">
                    <div class="post__indicators">
                      <div class="post__buttons">
                        <a
                          class="post__indicator post__indicator--likes button"
                          href="/like-add.php?post=<?= $id ?>"
                          title="Лайк"
                        >
                          <svg class="post__indicator-icon" width="20" height="17">
                            <use xlink:href="#icon-heart"></use>
                          </svg>
                          <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                            <use xlink:href="#icon-heart-active"></use>
                          </svg>
                          <span><?= count($likes) ?></span>
                          <span class="visually-hidden">количество лайков</span>
                        </a>
                        <a class="post__indicator post__indicator--repost button" href="#" title="Репост">
                          <svg class="post__indicator-icon" width="19" height="17">
                            <use xlink:href="#icon-repost"></use>
                          </svg>
                          <span>5</span>
                          <span class="visually-hidden">количество репостов</span>
                        </a>
                      </div>
                      <time
                        class="post__time"
                        datetime="<?= strtotime($dt_add) ?>"
                        title="<?= date_format(date_create($dt_add), 'd.m.Y H:m'); ?>"
                      >
                        <?= relative_time($current_time - strtotime($dt_add)); ?> назад
                      </time>
                    </div>
                    <?php if (count($hash_tags) > 0): ?>
                      <ul class="post__tags">
                        <?php foreach ($hash_tags as $tag): ?>
                          <li>
                            <a href="<?= '/search.php?search=' . urlencode("#$tag")  ?>">
                              <?= "#$tag" ?>
                            </a>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </footer>
                  <?php if(count($comments) > 0): ?>
                    <div class="comments">
                      <div class="comments__list-wrapper">
                        <ul class="comments__list">
                          <?php foreach($comments as $comment): ?>
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
                    </div>
                  <?php endif; ?>
                  <form class="comments__form form" action="/comment-add.php" method="post">
                    <div class="comments__my-avatar">
                      <img class="comments__picture" src="img/<?= $current_user['avatar'] ?>" alt="Аватар пользователя">
                    </div>
                    <div class="form__input-section<?= add_class($is_comment_add_error, 'form__input-section--error') ?>">
                      <input type="hidden" name="post-id" value="<?= $id ?>">
                      <input type="hidden" name="user-id" value="<?= $current_user['id'] ?>">
                      <input type="hidden" name="author-id" value="<?= $author_id ?>">
                      <textarea name="comment-content" class="comments__textarea form__textarea form__input" placeholder="Ваш комментарий"></textarea>
                      <label class="visually-hidden">Ваш комментарий</label>
                      <?php if ($is_comment_add_error): ?>
                        <button class="form__error-button button" type="button">!</button>
                        <div class="form__error-text">
                          <h3 class="form__error-title">Ошибка валидации</h3>
                          <p class="form__error-desc"><?= $comment_error['error'] ?></p>
                        </div>
                      <?php endif; ?>
                    </div>
                    <button class="comments__submit button button--green" type="submit">Отправить</button>
                  </form>
                </article>
              <?php endforeach; ?>
            </section>
          <?php endif ?>

          <?php if($tab === 'likes'): ?>
            <section class="profile__likes tabs__content tabs__content--active">
              <h2 class="visually-hidden">Лайки</h2>
              <ul class="profile__likes-list">
                <?php foreach($posts as $post): ?>
                  <li class="post-mini post-mini--<?= $post['type'] ?? '' ?> post user">
                    <div class="post-mini__user-info user__info">
                      <div class="post-mini__avatar user__avatar">
                        <a
                          class="user__avatar-link"
                          href="/profile.php?id=<?= $post['likes'][0]['user_id'] ?? '#' ?>"
                        >
                          <img class="post-mini__picture user__picture" src="img/<?= $post['likes'][0]['avatar'] ?>" alt="Аватар пользователя">
                        </a>
                      </div>
                      <div class="post-mini__name-wrapper user__name-wrapper">
                        <a
                          class="post-mini__name user__name"
                          href="/profile.php?id=<?= $post['likes'][0]['user_id'] ?? '#' ?>"
                        >
                          <span><?= $post['likes'][0]['login'] ?></span>
                        </a>
                        <div class="post-mini__action">
                          <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                          <time
                            class="post-mini__time user__additional"
                            datetime="<?= $post['likes'][0]['dt_add'] ?>"
                          >
                            <?= relative_time(time() - strtotime($post['likes'][0]['dt_add'])) ?> назад
                          </time>
                        </div>
                      </div>
                    </div>
                    <div class="post-mini__preview">
                      <a
                        class="post-mini__link"
                        href="/post.php?id=<?= $post['id'] ?>"
                        title="Перейти на публикацию"
                      >
                        <?php if($post['type'] === 'photo'): ?>
                          <div class="post-mini__image-wrapper">
                            <img
                              class="post-mini__image"
                              src="img/<?= htmlspecialchars($post['image'] ?? '') ?>"
                              width="109"
                              height="109"
                              alt="Превью публикации"
                            >
                          </div>
                          <span class="visually-hidden">Фото</span>
                        <?php elseif($post['type'] === 'text'): ?>
                          <span class="visually-hidden">Текст</span>
                          <svg class="post-mini__preview-icon" width="20" height="21">
                            <use xlink:href="#icon-filter-text"></use>
                          </svg>
                        <?php elseif($post['type'] === 'quote'): ?>
                          <span class="visually-hidden">Цитата</span>
                          <svg class="post-mini__preview-icon" width="21" height="20">
                            <use xlink:href="#icon-filter-quote"></use>
                          </svg>
                        <?php elseif($post['type'] === 'link'): ?>
                          <span class="visually-hidden">Ссылка</span>
                          <svg class="post-mini__preview-icon" width="21" height="20">
                            <use xlink:href="#icon-filter-link"></use>
                          </svg>
                        <?php elseif($post['type'] === 'video'): ?>
                          <div class="post-mini__image-wrapper">
                            <img class="post-mini__image" src="img/coast-small.png" width="109" height="109" alt="Превью публикации">
                            <span class="post-mini__play-big">
                            <svg class="post-mini__play-big-icon" width="12" height="13">
                              <use xlink:href="#icon-video-play-big"></use>
                            </svg>
                          </span>
                          </div>
                          <span class="visually-hidden">Видео</span>
                        <?php endif ?>
                      </a>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif ?>

          <?php if($tab === 'subscriptions'): ?>
            <section class="profile__subscriptions tabs__content tabs__content--active">
              <h2 class="visually-hidden">Подписки</h2>
              <ul class="profile__subscriptions-list">
                <?php foreach($followings as $following): ?>
                  <li class="post-mini post-mini--photo post user">
                    <div class="post-mini__user-info user__info">
                      <div class="post-mini__avatar user__avatar">
                        <a class="user__avatar-link" href="/profile.php?id=<?= $following['id'] ?? '' ?>">
                          <img class="post-mini__picture user__picture" src="img/<?= $following['avatar'] ?? '' ?>" alt="Аватар пользователя">
                        </a>
                      </div>
                      <div class="post-mini__name-wrapper user__name-wrapper">
                        <a class="post-mini__name user__name" href="/profile.php?id=<?= $following['id'] ?? '' ?>">
                          <span><?= $following['login'] ?? '' ?></span>
                        </a>
                        <time
                          class="post-mini__time user__additional"
                          datetime="<?= strtotime($following['dt_add']) ?>"
                          title="<?= date_format(date_create($following['dt_add']), 'd.m.Y H:m'); ?>"
                        >
                          <?= relative_time($current_time - strtotime($following['dt_add'])); ?> на сайте
                        </time>
                      </div>
                    </div>
                    <div class="post-mini__rating user__rating">
                      <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                        <span class="post-mini__rating-amount user__rating-amount">
                          <?= $following['posts_count'] ?>
                        </span>
                        <span class="post-mini__rating-text user__rating-text">
                          <?= get_noun_plural_form($following['posts_count'], 'публикация', 'публикации', 'публикаций') ?>
                        </span>
                      </p>
                      <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                        <span class="post-mini__rating-amount user__rating-amount">
                          <?= $following['followers_count'] ?>
                        </span>
                        <span class="post-mini__rating-text user__rating-text">
                          <?= get_noun_plural_form($following['followers_count'], 'подписчик', 'подписчика', 'подписчиков') ?>
                        </span>
                      </p>
                    </div>
                    <div class="post-mini__user-buttons user__buttons">
                      <a
                        class="post-mini__user-button user__button user__button--subscription button button--<?= $following['isCurrentUserFollowing'] ? 'quartz' : 'main' ?>"
                        href="/subscription.php?id=<?= $following['id'] ?>"
                      >
                        <?= $following['isCurrentUserFollowing'] ? 'Отписаться' : 'Подписаться' ?>
                      </a>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>
