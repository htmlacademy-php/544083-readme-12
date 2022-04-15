<?php
$errors = $errors ?? [];
$values = $values ?? [];
?>

<section class="adding-post__photo tabs__content tabs__content--active">
  <h2 class="visually-hidden">Форма добавления фото</h2>
  <form class="adding-post__form form" action="/add.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="type" value="photo">
    <div class="form__text-inputs-wrapper">
      <div class="form__text-inputs">
        <?php print(include_template('components/adding-post/form/components/title.php', [
          'error' => $errors['post-title'] ?? '',
          'value' => $values['post-title'] ?? '',
        ])) ?>
        <div class="adding-post__input-wrapper form__input-wrapper<?= add_class(!empty($errors['photo-url']), 'form__input-section--error') ?>">
          <label class="adding-post__label form__label" for="photo-url">Ссылка из интернета</label>
          <div class="form__input-section">
            <input
              class="adding-post__input form__input"
              id="photo-url"
              type="text"
              name="photo-url"
              value="<?= $values['photo-url'] ?? '' ?>"
              placeholder="Введите ссылку"
            >
            <?php print(include_template('components/adding-post/form/components/form-error.php', [
              'error' => $errors['photo-url'] ?? '',
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
    <div class="adding-post__input-file-container form__input-container form__input-container--file">
      <div class="adding-post__input-file-wrapper form__input-file-wrapper">
        <div class="adding-post__file-zone adding-post__file-zone--photo form__file-zone">
          <input
            class="adding-post__input-file form__input-file"
            id="post-photo"
            type="file"
            name="post-photo"
            value=""
          >
          <div class="form__file-zone-text">
            <span>Перетащите фото сюда</span>
          </div>
        </div>
        <!--
        Error js see https://htmlacademy-php.slack.com/archives/C0107N6HW7R/p1617822793005500
        <button class="adding-post__input-file-button form__input-file-button form__input-file-button--photo button" type="button">
          <span>Выбрать фото</span>
          <svg class="adding-post__attach-icon form__attach-icon" width="10" height="20">
            <use xlink:href="#icon-attach"></use>
          </svg>
        </button>-->
      </div>
      <div class="adding-post__file adding-post__file--photo form__file dropzone-previews"></div>
    </div>
    <?php print(include_template('components/adding-post/form/components/action.php')) ?>
  </form>
</section>
