<?php
$dialogs = $dialogs ?? [];
$current_dialog_user = $current_dialog_user ?? [];
$current_user = $current_user ?? [];
$current_dialog = $current_dialog ?? [];
$error = $error ?? [];
?>

<main class="page__main page__main--messages">
  <h1 class="visually-hidden">Личные сообщения</h1>
  <section class="messages tabs">
    <h2 class="visually-hidden">Сообщения</h2>
    <?php if (count($dialogs) > 0): ?>
      <div class="messages__contacts">
        <ul class="messages__contacts-list tabs__list">
          <?php foreach ($dialogs as $dialog): ?>
            <?php
              $is_active = $dialog['id'] === $current_dialog_user['id'];
              $active_class = add_class($is_active, 'tabs__item--active messages__contacts-tab--active')
            ?>
            <li class="messages__contacts-item">
              <a
                class="messages__contacts-tab tabs__item<?= $active_class ?>"
                href="/messages.php?id=<?= $dialog['id'] ?? '' ?>"
              >
                <div class="messages__avatar-wrapper">
                  <img class="messages__avatar" src="img/<?= $dialog['avatar'] ?? '' ?>" alt="Аватар пользователя">
                  <?php if(!$is_active && boolval($dialog['unread_count'])): ?>
                    <i class="messages__indicator">
                      <?= $dialog['unread_count'] ?>
                    </i>
                  <?php endif; ?>
                </div>
                <div class="messages__info">
                    <span class="messages__contact-name">
                      <?= $dialog['login'] ?? '' ?>
                    </span>
                  <div class="messages__preview">
                    <p class="messages__preview-text">
                      <?= htmlspecialchars($dialog['content'] ?? '') ?>
                    </p>
                    <time class="messages__preview-time" datetime="<?= $dialog['dt_add'] ?? '' ?>">
                      <?= get_message_date($dialog['dt_add'] ?? '') ?>
                    </time>
                  </div>
                </div>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="messages__chat">
        <div class="messages__chat-wrapper">
          <?php if (count($current_dialog) > 0): ?>
            <ul class="messages__list tabs__content tabs__content--active">
              <?php foreach($current_dialog as $item): ?>
                <?php
                $is_current_user_sender = $item['sender_id'] === $current_user['id'];
                $user = $is_current_user_sender ? $current_user : $current_dialog_user;
                ?>
                <li class="messages__item<?= add_class($is_current_user_sender, 'messages__item--my') ?>">
                  <div class="messages__info-wrapper">
                    <div class="messages__item-avatar">
                      <a class="messages__author-link" href="/profile.php?id=<?= $user['id'] ?>">
                        <img class="messages__avatar" src="img/<?= $user['avatar'] ?>" alt="Аватар пользователя">
                      </a>
                    </div>
                    <div class="messages__item-info">
                      <a class="messages__author" href="/profile.php?id=<?= $user['id'] ?>">
                        <?= $user['login'] ?>
                      </a>
                      <time class="messages__time" datetime="<?= $item['dt_add'] ?>">
                        <?= get_message_date($item['dt_add'] ?? '') ?>
                      </time>
                    </div>
                  </div>
                  <p class="messages__text">
                    <?= $item['content'] ?>
                  </p>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            Сообщений нет
          <?php endif; ?>
        </div>
        <div class="comments">
          <form class="comments__form form" action="/messages.php?id=<?= $current_dialog_user['id'] ?>" method="post">
            <div class="comments__my-avatar">
              <img class="comments__picture" src="img/<?= $current_user['avatar'] ?>" alt="Аватар пользователя">
            </div>
            <div class="form__input-section<?= add_class(count($error) > 0, 'form__input-section--error') ?>">
              <textarea class="comments__textarea form__textarea form__input" name="message-content" placeholder="Ваше сообщение"></textarea>
              <label class="visually-hidden">Ваше сообщение</label>
              <?php if (count($error) > 0): ?>
                <button class="form__error-button button" type="button">!</button>
                <div class="form__error-text">
                  <h3 class="form__error-title"><?= $error['label'] ?? '' ?></h3>
                  <p class="form__error-desc"><?= $error['error'] ?? '' ?></p>
                </div>
              <?php endif; ?>
            </div>
            <button class="comments__submit button button--green" type="submit">Отправить</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      Сообщений нет
    <?php endif; ?>
  </section>
</main>