<?php
$errors = $errors ?? [];
$values = $values ?? [];
?>

<section class="adding-post__quote tabs__content tabs__content--active">
  <h2 class="visually-hidden">Форма добавления цитаты</h2>
  <form class="adding-post__form form" action="/add.php" method="post">
    <input type="hidden" name="type" value="quote">
    <div class="form__text-inputs-wrapper">
      <div class="form__text-inputs">
        <?php print(include_template('components/adding-post/form/components/title.php', [
          'error' => $errors['post-title'] ?? '',
          'value' => $values['post-title'] ?? '',
        ])) ?>
        <div class="adding-post__input-wrapper form__textarea-wrapper<?= add_class(!empty($errors['cite-text']), 'form__input-section--error') ?>">
          <label class="adding-post__label form__label" for="cite-text">Текст цитаты <span class="form__input-required">*</span></label>
          <div class="form__input-section">
            <textarea
              class="adding-post__textarea adding-post__textarea--quote form__textarea form__input"
              id="cite-text"
              name="cite-text"
              placeholder="Текст цитаты"
            ><?= $values['cite-text'] ?? '' ?></textarea>
            <?php print(include_template('components/form/form-error.php', [
              'error' => $errors['cite-text'] ?? '',
            ])); ?>
          </div>
        </div>
        <div class="adding-post__textarea-wrapper form__input-wrapper<?= add_class(!empty($errors['quote-author']), 'form__input-section--error') ?>">
          <label class="adding-post__label form__label" for="quote-author">Автор <span class="form__input-required">*</span></label>
          <div class="form__input-section">
            <input
              class="adding-post__input form__input"
              id="quote-author"
              type="text"
              name="quote-author"
              placeholder="Автор цитаты"
              value="<?= $values['quote-author'] ?? '' ?>"
            >
            <?php print(include_template('components/form/form-error.php', [
              'error' => $errors['quote-author'] ?? '',
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
