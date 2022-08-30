<?php
$errors = $errors ?? [];
$values = $values ?? [];
?>

<section class="adding-post__video tabs__content tabs__content--active">
  <h2 class="visually-hidden">Форма добавления видео</h2>
  <form class="adding-post__form form" action="#" method="post" enctype="multipart/form-data">
    <input type="hidden" name="type" value="video">
    <div class="form__text-inputs-wrapper">
      <div class="form__text-inputs">
        <?php print(include_template('components/adding-post/form/components/title.php', [
          'error' => $errors['post-title'] ?? '',
          'value' => $values['post-title'] ?? '',
        ])) ?>
        <div class="adding-post__input-wrapper form__input-wrapper<?= add_class(!empty($errors['video-url']), 'form__input-section--error') ?>">
          <label class="adding-post__label form__label" for="video-url">Ссылка youtube <span class="form__input-required">*</span></label>
          <div class="form__input-section">
            <input
              class="adding-post__input form__input"
              id="video-url"
              type="text"
              name="video-url"
              placeholder="Введите ссылку"
              value="<?= htmlspecialchars($values['video-url'] ?? '') ?>"
            >
            <?php print(include_template('components/form/form-error.php', [
              'error' => $errors['video-url'] ?? '',
            ])); ?>
          </div>
        </div>
        <?php print(include_template('components/adding-post/form/components/hash-tags.php', [
          'error' => $errors['hash-tags'] ?? '',
          'value' => $values['hash-tags'] ?? '',
        ])) ?>
      </div>
      <?php print(include_template('components/form/invalid-block.php', [
        'errors' => $errors
      ])) ?>
    </div>
    <?php print(include_template('components/adding-post/form/components/action.php')) ?>
  </form>
</section>
