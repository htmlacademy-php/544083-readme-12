/* Добавление пользователей */
INSERT INTO users (email, login, password, avatar)
VALUES
   ('email_1@mail.ru', 'Лариса', 'password_1', 'userpic-larisa-small.jpg'),
   ('email_2@mail.ru', 'Виктор', 'password_2', 'userpic.jpg'),
   ('email_3@mail.ru', 'Владик', 'password_3', 'userpic-mark.jpg');

/* Добавление типов постов */
INSERT INTO post_types (name, class_name)
VALUES
    ('quote', 'post-quote'),
    ('text', 'post-text'),
    ('photo', 'post-photo'),
    ('link', 'post-link'),
    ('video', 'post-video');

/* Добавление постов */
INSERT INTO posts (author_id, title, type_id, views, text, quote_author, image, link)
VALUES
   (1, 'Цитата', 1, 1, 'Мы в жизни любим только раз, а после ищем лишь похожих', 'Лариса', null, null),
   (2, 'Игра престолов', 2, 2, 'Не могу дождаться начала финального сезона своего любимого сериала!', null, null, null),
   (3, 'Наконец, обработал фотки!', 3, 3, null, null, 'rock-medium.jpg', null),
   (1, 'Моя мечта', 3, 4, null, null, 'coast-medium.jpg', null),
   (2, 'Лучшие курсы', 4, 5, null, null, null, 'www.htmlacademy.ru');

/* Добавление комментариев */
INSERT INTO comments (content, author_id, post_id)
VALUES
    ('коммент к посту 1', 1, 1),
    ('коммент к посту 2', 1, 1),
    ('коммент к посту 3', 1, 2),
    ('коммент к посту 4', 2, 2),
    ('коммент к посту 5', 2, 3),
    ('коммент к посту 6', 2, 3),
    ('коммент к посту 7', 3, 4),
    ('коммент к посту 8', 3, 4),
    ('коммент к посту 9', 3, 5),
    ('коммент к посту 10', 3, 5);

/* Получение списка постов с сортировкой по популярности и вместе с именами авторов и типом контента */
SELECT p.id, type_id, views, login FROM posts p
JOIN users u ON author_id = u.id
ORDER BY views DESC;

/* получение список постов для конкретного пользователя */
SELECT id FROM posts WHERE author_id = 1;

/* получение списка постов для конкретного пользователя */
SELECT id FROM posts WHERE author_id = 1;

/* получение списка комментариев для одного поста, в комментариях должен быть логин пользователя */
SELECT c.id, login FROM comments c
JOIN users u ON author_id = u.id
WHERE post_id = 1;

/* добавиление лайка к посту */
INSERT INTO likes (user_id, post_id) VALUES (1, 1);

/* подписка на пользователя. */
INSERT INTO subscriptions (following_id, follower_id) VALUES (1, 2);