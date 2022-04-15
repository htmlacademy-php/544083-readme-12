<?php
$errors = $errors ?? [];
$values = $values ?? [];
?>

<section class="adding-post__text tabs__content tabs__content--active">
  <h2 class="visually-hidden">Форма добавления текста</h2>
  <form class="adding-post__form form" action="#" method="post">
    <input type="hidden" name="type" value="text">
    <div class="form__text-inputs-wrapper">
      <div class="form__text-inputs">
        <?php print(include_template('components/adding-post/form/components/title.php', [
          'error' => $errors['post-title'] ?? '',
          'value' => $values['post-title'] ?? '',
        ])) ?>
        <div class="adding-post__textarea-wrapper form__textarea-wrapper">
          <label class="adding-post__label form__label" for="post-text">Текст поста <span class="form__input-required">*</span></label>
          <div class="form__input-section">
            <textarea
              class="adding-post__textarea form__textarea form__input<?= add_class(!empty($errors['post-text']), 'form__input-section--error') ?>"
              id="post-text"
              name="post-text"
              placeholder="Введите текст публикации"
            ><?= $values['post-text'] ?? '' ?></textarea>
            <?php print(include_template('components/adding-post/form/components/form-error.php', [
              'error' => $errors['post-text'] ?? '',
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