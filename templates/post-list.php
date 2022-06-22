<?php
$get_params = $get_params ?? null;
$need_pagination = $need_pagination ?? null;
$prev_page_link = $prev_page_link ?? null;
$next_page_link = $next_page_link ?? null;
?>

<section class="page__main page__main--popular">
  <div class="container">
    <h1 class="page__title page__title--popular">Популярное</h1>
  </div>
  <div class="popular container">
    <div class="popular__filters-wrapper">
      <div class="popular__sorting sorting">
        <b class="popular__sorting-caption sorting__caption">Сортировка:</b>
        <ul class="popular__sorting-list sorting__list">
          <li class="sorting__item sorting__item--popular">
            <a
              class="sorting__link<?= ($sort ?? '') === 'views' ?  ' sorting__link--active' : ''?>"
              href="?<?= http_build_query(['sort' => 'views', 'tab' => $tab ?? '']) ?>"
            >
              <span>Популярность</span>
              <svg class="sorting__icon" width="10" height="12">
                <use xlink:href="#icon-sort"></use>
              </svg>
            </a>
          </li>
          <li class="sorting__item">
            <a
              class="sorting__link<?= ($sort ?? '') === 'likes_count' ?  ' sorting__link--active' : ''?>"
              href="?<?= http_build_query(['sort' => 'likes_count', 'tab' => $tab ?? '']) ?>"
            >
              <span>Лайки</span>
              <svg class="sorting__icon" width="10" height="12">
                <use xlink:href="#icon-sort"></use>
              </svg>
            </a>
          </li>
          <li class="sorting__item">
            <a
              class="sorting__link<?= ($sort ?? '') === 'dt_add' ?  ' sorting__link--active' : ''?>"
              href="?<?= http_build_query(['sort' => 'dt_add', 'tab' => $tab ?? '']) ?>"
            >
              <span>Дата</span>
              <svg class="sorting__icon" width="10" height="12">
                <use xlink:href="#icon-sort"></use>
              </svg>
            </a>
          </li>
        </ul>
      </div>
      <div class="popular__filters filters">
        <b class="popular__filters-caption filters__caption">Тип контента:</b>
        <ul class="popular__filters-list filters__list">
          <li class="popular__filters-item popular__filters-item--all filters__item filters__item--all">
            <a
              class="filters__button filters__button--ellipse filters__button--all<?= ($is_all_tab ?? '') ? ' filters__button--active' : ''  ?>"
              href="?sort=<?=$sort ?? ''?>"
            >
              <span>Все</span>
            </a>
          </li>
          <?php if (!empty($post_types)): ?>
            <?php foreach ($post_types as $post_type): ?>
              <?php
              $id = $post_type['id'] ?? null;
              $type = $post_type['type'] ?? null;
              $name = $post_type['name'] ?? null;
              $active_class = ($tab ?? '') == $id ? ' filters__button--active' : '';
              ?>
              <?php if ($type && $name): ?>
                <li class="popular__filters-item filters__item">
                  <a
                    class="filters__button button filters__button--<?= $type . $active_class ?>"
                    href="?tab=<?= $id ?>"
                  >
                  <span class="visually-hidden">
                    <?= $name ?>
                  </span>
                    <svg class="filters__icon" width="22" height="18">
                      <use xlink:href="#icon-filter-<?= $type ?>"></use>
                    </svg>
                  </a>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <?php if (!empty($posts)): ?>
      <?php $current_time = time(); ?>
      <div class="popular__posts">
        <?php foreach ($posts as $post): ?>
          <?php
          $title = htmlspecialchars($post['title'] ?? '');
          $type = $post['type'] ?? '';
          $text = htmlspecialchars($post['text']  ?? '');
          $quote_author = htmlspecialchars($post['quote_author'] ?? '');
          $link = htmlspecialchars($post['link'] ?? '');
          $image = htmlspecialchars($post['image'] ?? '');
          $avatar = htmlspecialchars($post['avatar'] ?? '');
          $author = $post['author'] ?? '';
          $author_id = $post['author_id'] ?? '';
          $id = $post['id'] ?? '';
          $likes_count = count($post['likes'] ?? []);
          $comments_count = count($post['comments'] ?? []);
          $dt_add = $post['dt_add'] ?? '';
          ?>
          <article class="popular__post post post-<?= $type ?>">
            <header class="post__header">
              <h2>
                <a href="/post.php?id=<?=$id?>"><?= $title ?></a>
              </h2>
            </header>
            <?php if (boolval($type)): ?>
              <div class="post__main">
                <?php if ($type === 'text'): ?>
                  <?php $text = cropping_text($text); ?>
                  <p><?= $text['str'] ?></p>
                  <?php if (!$text['isLimited']):  ?>
                    <a class="post-text__more-link" href="#">Читать далее</a>
                  <?php endif; ?>
                <?php elseif ($type === 'quote'): ?>
                  <blockquote>
                    <p><?= $text ?></p>
                    <cite><?= $quote_author ?></cite>
                  </blockquote>
                <?php elseif ($type === 'photo'): ?>
                  <div class="post-photo__image-wrapper">
                    <img src="img/<?= $image ?>" alt="Фото от пользователя <?= $author ?>" width="360" height="240">
                  </div>
                <?php elseif ($type === 'link'): ?>
                  <div class="post-link__wrapper">
                    <a class="post-link__external" href="<?= $link ?>" title="Перейти по ссылке">
                      <div class="post-link__info-wrapper">
                        <div class="post-link__icon-wrapper">
                          <img src="https://www.google.com/s2/favicons?domain=vitadental.ru" alt="Иконка">
                        </div>
                        <div class="post-link__info">
                          <h3><?= $title ?></h3>
                        </div>
                      </div>
                      <span><?= $link ?></span>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <footer class="post__footer">
              <div class="post__author">
                <a
                  class="post__author-link"
                  href="/profile.php?id=<?= $author_id ?>"
                  title="<?= $author ?>"
                >
                  <div class="post__avatar-wrapper">
                    <img class="post__author-avatar" src="img/<?= $avatar ?>" alt="Аватар пользователя <?= $author ?>">
                  </div>
                  <div class="post__info">
                    <b class="post__author-name"><?= $author ?></b>
                    <time
                      class="post__time"
                      datetime="<?= strtotime($dt_add) ?>"
                      title="<?= date_format(date_create($dt_add), 'd.m.Y H:m'); ?>"
                    >
                      <?= relative_time($current_time - strtotime($dt_add)); ?> назад
                    </time>
                  </div>
                </a>
              </div>
              <div class="post__indicators">
                <div class="post__buttons">
                  <a
                    class="post__indicator post__indicator--likes button"
                    href="/like.php?post=<?= $id ?>"
                    title="Лайк"
                  >
                    <svg class="post__indicator-icon" width="20" height="17">
                      <use xlink:href="#icon-heart"></use>
                    </svg>
                    <svg class="post__indicator-icon post__indicator-icon--like-active" width="20"
                         height="17">
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
                </div>
              </div>
            </footer>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($need_pagination): ?>
        <div class="popular__page-links">
          <a
            <?= !$prev_page_link ? 'style="pointer-events: none"' : ''  ?>
            class="popular__page-link popular__page-link--prev button button--gray"
            href="<?= $prev_page_link ?>"
          >
            Предыдущая страница
          </a>
          <a
            <?= !$next_page_link ? 'style="pointer-events: none"' : ''  ?>
            class="popular__page-link popular__page-link--next button button--gray"
            href="<?= $next_page_link ?>"
          >
            Следующая страница
          </a>
        </div>
      <?php endif; ?>
    <?php else: ?>
      Постов нет
    <?php endif; ?>
  </div>
</section>
