CREATE DATABASE readme
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE readme;

SET GLOBAL time_zone = 'Europe/Moscow';
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

CREATE TABLE users (
   id INT AUTO_INCREMENT PRIMARY KEY,
   email VARCHAR (128) NOT NULL UNIQUE,
   login VARCHAR (32) NOT NULL UNIQUE,
   password CHAR (64) NOT NULL,
   avatar VARCHAR (255) NULL,
   dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX email_ix ON users(email);

CREATE TABLE post_types (
   id INT AUTO_INCREMENT PRIMARY KEY,
   type VARCHAR (64) NOT NULL UNIQUE,
   name VARCHAR (64) NOT NULL UNIQUE
);

CREATE UNIQUE INDEX post_type_ix ON post_types(type);
CREATE UNIQUE INDEX post_name_ix ON post_types(name);

CREATE TABLE hashtags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR (64) NOT NULL UNIQUE
);

CREATE UNIQUE INDEX hashtag_name_ix ON hashtags(name);

CREATE TABLE posts (
   id INT AUTO_INCREMENT PRIMARY KEY,
   author_id INT NOT NULL,
   title VARCHAR (128) NOT NULL,
   text TEXT NULL,
   image VARCHAR (255) NULL,
   video VARCHAR (255) NULL,
   link VARCHAR (255) NULL,
   views INT NULL,
   quote_author VARCHAR (128) NULL,
   type_id INT NOT NULL,
   dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   CONSTRAINT post_author_ref FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE,
   CONSTRAINT post_type_ref FOREIGN KEY (type_id) REFERENCES post_types (id) ON DELETE CASCADE
);

CREATE FULLTEXT INDEX post_ft_ic ON posts(title, text, quote_author);

CREATE TABLE posts_by_hashtags (
    post_id INT NOT NULL,
    hash_tag_id INT NOT NULL,
    CONSTRAINT ht_post_ref FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT ht_tag_ref FOREIGN KEY (hash_tag_id) REFERENCES hashtags (id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (post_id, hash_tag_id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    author_id INT NOT NULL,
    post_id INT NOT NULL,
    CONSTRAINT comment_author_ref FOREIGN KEY (author_id) REFERENCES users (id),
    CONSTRAINT comment_post_ref FOREIGN KEY (post_id) REFERENCES posts (id)
);

CREATE TABLE likes (
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT like_author_ref FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT like_post_ref FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, post_id)
);

CREATE TABLE subscriptions (
   following_id INT NOT NULL,
   follower_id INT NOT NULL,
   CONSTRAINT sub_following_ref FOREIGN KEY (following_id) REFERENCES users (id) ON DELETE CASCADE,
   CONSTRAINT sub_follower_ref FOREIGN KEY (follower_id) REFERENCES users (id) ON DELETE CASCADE,
   PRIMARY KEY (following_id, follower_id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    content TEXT NOT NULL,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    CONSTRAINT msg_sender_ref FOREIGN KEY (sender_id) REFERENCES users (id),
    CONSTRAINT msg_recipient_ref FOREIGN KEY (recipient_id) REFERENCES users (id)  ON DELETE CASCADE
);

CREATE INDEX messages_content_ix ON messages(content(255));
