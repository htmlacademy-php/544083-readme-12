<?php
$errors = $errors ?? [];
$values = $values ?? [];
?>

<section class="adding-post__link tabs__content tabs__content--active">
  <h2 class="visually-hidden">Форма добавления ссылки</h2>
  <form class="adding-post__form form" action="/add.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="type" value="link">
    <div class="form__text-inputs-wrapper">
      <div class="form__text-inputs">
        <?php print(include_template('components/adding-post/form/components/title.php', [
          'error' => $errors['post-title'] ?? '',
          'value' => $values['post-title'] ?? '',
        ])) ?>
        <div class="adding-post__textarea-wrapper form__input-wrapper<?= add_class(!empty($errors['post-link']), 'form__input-section--error') ?>">
          <label class="adding-post__label form__label" for="post-link">Ссылка <span class="form__input-required">*</span></label>
          <div class="form__input-section">
            <input
              class="adding-post__input form__input"
              id="post-link"
              type="text"
              name="post-link"
              value="<?= $values['post-link'] ?? '' ?>"
              placeholder="Введите ссылку"
            >
            <?php print(include_template('components/adding-post/form/components/form-error.php', [
              'error' => $errors['post-link'] ?? '',
            ])); ?>
          </div>
        </div>
        <?php print(include_template('components/adding-post/form/components/hash-tags.php', [
          'error' => $errors['hash-tags'] ?? '',
          'value' => $values['hash-tags'] ?? '',
        ])) ?>
      </div>
      <?php print(include_template('components/adding-post/form/components/invalid-block.php', [
        'errors' => $errors
      ])) ?>
    </div>
    <?php print(include_template('components/adding-post/form/components/action.php')) ?>
  </form>
</section>