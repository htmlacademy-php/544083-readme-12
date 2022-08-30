<?php
$error = $error ?? '';
$value = $value ?? '';
?>

<div class="adding-post__input-wrapper form__input-wrapper<?= add_class(!empty($error), 'form__input-section--error') ?>">
  <label class="adding-post__label form__label" for="cite-tags">Теги</label>
  <div class="form__input-section">
    <input
      class="adding-post__input form__input"
      id="cite-tags"
      type="text"
      name="hash-tags"
      placeholder="Введите теги"
      value="<?= htmlspecialchars($value) ?>"
    >
    <?php print(include_template('components/form/form-error.php', [
      'error' => $error,
    ])); ?>
  </div>
</div>