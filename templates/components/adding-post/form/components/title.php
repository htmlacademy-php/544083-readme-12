<?php
$error = $error ?? '';
$value = $value ?? '';
?>

<div class="adding-post__input-wrapper form__input-wrapper<?= add_class(!empty($error), 'form__input-section--error') ?>">
  <label class="adding-post__label form__label" for="post-title">Заголовок <span class="form__input-required">*</span></label>
  <div class="form__input-section">
    <input
      class="adding-post__input form__input"
      id="post-title"
      type="text"
      name="post-title"
      placeholder="Введите заголовок"
      value="<?= $value ?>"
    >
    <?php print(include_template('components/adding-post/form/components/form-error.php', [
      'error' => $error,
    ])); ?>
  </div>
</div>
