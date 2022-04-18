<?php
$post_types = $post_types ?? [];
$tab = $tab ?? 'photo';
$errors = $errors ?? [];
$values = $values ?? [];
?>

<main class="page__main page__main--adding-post">
  <div class="page__main-section">
    <div class="container">
      <h1 class="page__title page__title--adding-post">Добавить публикацию</h1>
    </div>
    <div class="adding-post container">
      <div class="adding-post__tabs-wrapper tabs">
        <div class="adding-post__tabs filters">
          <ul class="adding-post__tabs-list filters__list tabs__list">
            <?php foreach ($post_types as $post_type): ?>
              <?php
                $name = $post_type['name'] ?? '';
                $type = $post_type['type'] ?? '';
                $active_class = add_class($type === $tab, 'filters__button--active tabs__item tabs__item--active');
              ?>
              <li class="adding-post__tabs-item filters__item">
                <a
                  class="adding-post__tabs-link button filters__button filters__button--<?= $type . $active_class ?>"
                  href="?tab=<?= $type ?>"
                >
                  <svg class="filters__icon" width="22" height="18">
                    <use xlink:href="#icon-filter-<?= $type ?>"></use>
                  </svg>
                  <span><?= $name ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="adding-post__tab-content">
          <?php print(include_template("components/adding-post/form/$tab.php", [
            'errors' => $errors,
            'values' => $values,
          ])) ?>
        </div>
      </div>
    </div>
  </div>
</main>