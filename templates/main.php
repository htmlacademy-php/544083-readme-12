<div class="container">
  <h1 class="page__title page__title--popular">Популярное</h1>
</div>
<div class="popular container">
  <div class="popular__filters-wrapper">
    <div class="popular__sorting sorting">
      <b class="popular__sorting-caption sorting__caption">Сортировка:</b>
      <ul class="popular__sorting-list sorting__list">
        <li class="sorting__item sorting__item--popular">
          <a class="sorting__link sorting__link--active" href="#">
            <span>Популярность</span>
            <svg class="sorting__icon" width="10" height="12">
              <use xlink:href="#icon-sort"></use>
            </svg>
          </a>
        </li>
        <li class="sorting__item">
          <a class="sorting__link" href="#">
            <span>Лайки</span>
            <svg class="sorting__icon" width="10" height="12">
              <use xlink:href="#icon-sort"></use>
            </svg>
          </a>
        </li>
        <li class="sorting__item">
          <a class="sorting__link" href="#">
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
          <a class="filters__button filters__button--ellipse filters__button--all filters__button--active"
             href="#">
            <span>Все</span>
          </a>
        </li>
        <li class="popular__filters-item filters__item">
          <a class="filters__button filters__button--photo button" href="#">
            <span class="visually-hidden">Фото</span>
            <svg class="filters__icon" width="22" height="18">
              <use xlink:href="#icon-filter-photo"></use>
            </svg>
          </a>
        </li>
        <li class="popular__filters-item filters__item">
          <a class="filters__button filters__button--video button" href="#">
            <span class="visually-hidden">Видео</span>
            <svg class="filters__icon" width="24" height="16">
              <use xlink:href="#icon-filter-video"></use>
            </svg>
          </a>
        </li>
        <li class="popular__filters-item filters__item">
          <a class="filters__button filters__button--text button" href="#">
            <span class="visually-hidden">Текст</span>
            <svg class="filters__icon" width="20" height="21">
              <use xlink:href="#icon-filter-text"></use>
            </svg>
          </a>
        </li>
        <li class="popular__filters-item filters__item">
          <a class="filters__button filters__button--quote button" href="#">
            <span class="visually-hidden">Цитата</span>
            <svg class="filters__icon" width="21" height="20">
              <use xlink:href="#icon-filter-quote"></use>
            </svg>
          </a>
        </li>
        <li class="popular__filters-item filters__item">
          <a class="filters__button filters__button--link button" href="#">
            <span class="visually-hidden">Ссылка</span>
            <svg class="filters__icon" width="21" height="18">
              <use xlink:href="#icon-filter-link"></use>
            </svg>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <?php if (!empty($popular_posts)): ?>
    <?php
      $current_time = time();
      function time_difference ($time): string {
        $minute = 60;
        $hour = $minute * 60;
        $day = $hour * 24;
        $week = $day * 7;
        $five_week = $week * 5;

        switch ($time) {
          case $time >= $five_week:
            $relative_time = $time / $five_week;
            $string_one = 'месяц';
            $string_two = 'месяца';
            $string_many = 'месяцев';
            break;
          case $time >= $week && $time < $five_week:
            $relative_time = $time / $week;
            $string_one = 'неделю';
            $string_two = 'недели';
            $string_many = 'недель';
            break;
          case $time >= $day && $time < $week:
            $relative_time = $time / $day;
            $string_one = 'день';
            $string_two = 'дня';
            $string_many = 'дней';
            break;
          case $time >= $hour && $time < $day:
            $relative_time = $time / $hour;
            $string_one = 'час';
            $string_two = 'часа';
            $string_many = 'часов';
            break;
          default:
            $relative_time = $time / $minute;
            $string_one = 'минуту';
            $string_two = 'минуты';
            $string_many = 'минут';
        }

        $relative_time = floor($relative_time);

        return sprintf('%s %s назад', $relative_time, get_noun_plural_form($relative_time, $string_one, $string_two, $string_many));
      };
    ?>
    <div class="popular__posts">
      <?php foreach ($popular_posts as $key => $post): ?>
        <?php
          $title = htmlspecialchars($post['title']) ?? '';
          $type = $post['type'] ?? '';
          $content = htmlspecialchars($post['content']) ?? '';
          $avatar = htmlspecialchars($post['avatar']) ?? '';
          $author = htmlspecialchars($post['author']) ?? '';
          $time = generate_random_date($key);
        ?>

        <article class="popular__post post <?= $type ?>">
          <header class="post__header">
            <h2>
              <?= $title ?>
            </h2>
          </header>
          <?php if ($type && isset($post_types)): ?>
            <div class="post__main">
              <?php if ($type === $post_types['text']): ?>
                <?php $content = cropping_text($content); ?>
                <p><?= $content['str'] ?></p>
                <?php if (!$content['isLimited']):  ?>
                  <a class="post-text__more-link" href="#">Читать далее</a>
                <?php endif; ?>
              <?php elseif ($type === $post_types['quote']): ?>
                <blockquote>
                  <p><?= $content ?></p>
                  <cite><?= $author ?></cite>
                </blockquote>
              <?php elseif ($type === $post_types['photo']): ?>
                <div class="post-photo__image-wrapper">
                  <img src="img/<?= $content ?>" alt="Фото от пользователя <?= $author ?>" width="360" height="240">
                </div>
              <?php elseif ($type === $post_types['link']): ?>
                <div class="post-link__wrapper">
                  <a class="post-link__external" href="<?= $content ?>" title="Перейти по ссылке">
                    <div class="post-link__info-wrapper">
                      <div class="post-link__icon-wrapper">
                        <img src="https://www.google.com/s2/favicons?domain=vitadental.ru" alt="Иконка">
                      </div>
                      <div class="post-link__info">
                        <h3><?= $title ?></h3>
                      </div>
                    </div>
                    <span><?= $content ?></span>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <footer class="post__footer">
            <div class="post__author">
              <a class="post__author-link" href="#" title="<?= $author ?>">
                <div class="post__avatar-wrapper">
                  <img class="post__author-avatar" src="img/<?= $avatar ?>" alt="Аватар пользователя <?= $author ?>">
                </div>
                <div class="post__info">
                  <b class="post__author-name"><?= $author ?></b>
                  <time
                    class="post__time"
                    datetime="<?= $time ?>"
                    title="<?= date_format(date_create($time), 'd.m.Y H:m'); ?>"
                  >
                    <?= time_difference($current_time - strtotime($time)); ?>
                  </time>
                </div>
              </a>
            </div>
            <div class="post__indicators">
              <div class="post__buttons">
                <a class="post__indicator post__indicator--likes button" href="#" title="Лайк">
                  <svg class="post__indicator-icon" width="20" height="17">
                    <use xlink:href="#icon-heart"></use>
                  </svg>
                  <svg class="post__indicator-icon post__indicator-icon--like-active" width="20"
                       height="17">
                    <use xlink:href="#icon-heart-active"></use>
                  </svg>
                  <span>0</span>
                  <span class="visually-hidden">количество лайков</span>
                </a>
                <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                  <svg class="post__indicator-icon" width="19" height="17">
                    <use xlink:href="#icon-comment"></use>
                  </svg>
                  <span>0</span>
                  <span class="visually-hidden">количество комментариев</span>
                </a>
              </div>
            </div>
          </footer>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
