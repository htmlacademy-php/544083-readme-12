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
      Постов нет
    <?php else: ?>
      <section class="feed">
        <h2 class="visually-hidden">Лента</h2>
        <div class="feed__main-wrapper">
          <div class="feed__wrapper">
            <?php print(include_template('components/posts.php', [
              'posts' => $posts,
              'class_name' => 'feed__post'
            ])) ?>
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
