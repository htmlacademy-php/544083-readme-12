<?php
$user = $user ?? [];
$posts = $posts ?? [];
$post_types = $post_types ?? [];
$is_all_tab = $is_all_tab ?? null;
$tab = $tab ?? '';
?>

<main class="page__main page__main--feed">
  <div class="container">
    <h1 class="page__title page__title--feed">Моя лента</h1>
  </div>
  <div class="page__main-wrapper container">
    <?php if(count($posts) === 0): ?>
      Вы не опубликовали ни одного поста

    <?php else: ?>
      <section class="feed">
        <h2 class="visually-hidden">Лента</h2>
        <div class="feed__main-wrapper">
          <div class="feed__wrapper">
            <?php $current_time = time(); ?>
            <?php foreach($posts as $post): ?>
              <?php
              $title = htmlspecialchars($post['title']) ?? '';
              $type = $post['type'] ?? '';
              $text = htmlspecialchars($post['text']) ?? '';
              $quote_author = htmlspecialchars($post['quote_author']) ?? '';
              $link = htmlspecialchars($post['link']) ?? '';
              $image = htmlspecialchars($post['image']) ?? '';
              $avatar = htmlspecialchars($post['avatar']) ?? '';
              $author = htmlspecialchars($post['author']) ?? '';
              $id = $post['id'] ?? '';
              $dt_add = $post['dt_add'] ?? '';
              $likes_count = $post['likes'] ?? 0;
              $comments_count = $post['comments_count'] ?? 0;
              ?>
              <article class="feed__post post post-<?= $type ?>">
                <header class="post__header post__author">
                  <a class="post__author-link" href="#" title="Автор">
                    <div class="post__avatar-wrapper">
                      <img
                        class="post__author-avatar"
                        src="img/<?= $avatar ?>"
                        alt="Аватар пользователя"
                        width="60"
                        height="60"
                      >
                    </div>
                    <div class="post__info">
                      <b class="post__author-name"><?= $author ?></b>
                      <span class="post__time">
                      <?= relative_time($current_time - strtotime($dt_add)); ?> назад
                    </span>
                    </div>
                  </a>
                </header>
                <div class="post__main">
                  <h2><a href="#"><?= $title ?></a></h2>
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
                <footer class="post__footer post__indicators">
                  <div class="post__buttons">
                    <a class="post__indicator post__indicator--likes button" href="#" title="Лайк">
                      <svg class="post__indicator-icon" width="20" height="17">
                        <use xlink:href="#icon-heart"></use>
                      </svg>
                      <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                        <use xlink:href="#icon-heart-active"></use>
                      </svg>
                      <span><?= $likes_count ?></span>
                      <span class="visually-hidden">количество лайков</span>
                    </a>
                    <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                      <svg class="post__indicator-icon" width="19" height="17">
                        <use xlink:href="#icon-comment"></use>
                      </svg>
                      <span><?= $comments_count ?></span>
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
                </footer>
              </article>
            <?php endforeach ?>
          </div>
        </div>
        <?php if(count($post_types) > 0): ?>
          <ul class="feed__filters filters">
            <li class="feed__filters-item filters__item">
              <a
                class="filters__button<?= add_class($is_all_tab, 'filters__button--active') ?>"
                href="<?= "?tab=all" ?>"
              >
                <span>Все</span>
              </a>
            </li>
            <?php foreach($post_types as $post_type): ?>
              <li class="feed__filters-item filters__item">
                <a
                  class="filters__button filters__button--<?= $post_type['type'] ?? '' ?> button<?= add_class($tab == $post_type['id'], 'filters__button--active') ?>"
                  href="?tab=<?= $post_type['id'] ?? '' ?>"
                >
                  <span class="visually-hidden">
                    <?= $post_type['name'] ?? '' ?>
                  </span>
                  <svg class="filters__icon" width="22" height="18">
                    <use xlink:href="#icon-filter-<?= $post_type['type'] ?? '' ?>"></use>
                  </svg>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    <?php endif ?>
    <aside class="promo">
      <article class="promo__block promo__block--barbershop">
        <h2 class="visually-hidden">Рекламный блок</h2>
        <p class="promo__text">
          Все еще сидишь на окладе в офисе? Открой свой барбершоп по нашей франшизе!
        </p>
        <a class="promo__link" href="#">
          Подробнее
        </a>
      </article>
      <article class="promo__block promo__block--technomart">
        <h2 class="visually-hidden">Рекламный блок</h2>
        <p class="promo__text">
          Товары будущего уже сегодня в онлайн-сторе Техномарт!
        </p>
        <a class="promo__link" href="#">
          Перейти в магазин
        </a>
      </article>
      <article class="promo__block">
        <h2 class="visually-hidden">Рекламный блок</h2>
        <p class="promo__text">
          Здесь<br> могла быть<br> ваша реклама
        </p>
        <a class="promo__link" href="#">
          Разместить
        </a>
      </article>
    </aside>
  </div>
</main>
