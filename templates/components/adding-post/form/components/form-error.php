<button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
<div class="form__error-text">
  <h3 class="form__error-title"><?= $error['label'] ?? '' ?></h3>
  <p class="form__error-desc"><?= $error['error'] ?? 'Что то пошло не так, попробуйте еще раз' ?></p>
</div>
