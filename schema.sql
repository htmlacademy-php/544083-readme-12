CREATE DATABASE readme
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE readme;

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
   name VARCHAR (64) NOT NULL UNIQUE,
   class_name VARCHAR (64) NOT NULL UNIQUE
);

CREATE UNIQUE INDEX post_type_ix ON post_types(name);
CREATE UNIQUE INDEX class_name_ix ON post_types(class_name);

CREATE TABLE hashtags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR (64) NOT NULL UNIQUE
);

CREATE UNIQUE INDEX hashtag_name_ix ON hashtags(name);

CREATE TABLE posts (
   id INT AUTO_INCREMENT PRIMARY KEY,
   author_id INT NOT NULL REFERENCES users(id),
   title VARCHAR (128) NOT NULL,
   text TEXT NULL,
   image VARCHAR (255) NULL,
   video VARCHAR (255) NULL,
   link VARCHAR (255) NULL,
   views INT NULL,
   quote_author VARCHAR (128) NULL,
   type_id INT NOT NULL REFERENCES post_types(id),
   dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX post_title_ix ON posts(title);
CREATE INDEX post_text_ix ON posts(text(255));
CREATE INDEX quote_author_ix ON posts(quote_author);

CREATE TABLE posts_by_hashtags (
    post_id INT NOT NULL REFERENCES posts(id),
    hash_tag_id INT NOT NULL REFERENCES hashtags(id),
    PRIMARY KEY (post_id, hash_tag_id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    author_id INT NOT NULL REFERENCES users(id),
    post_id INT NOT NULL REFERENCES posts(id)
);

CREATE TABLE likes (
    user_id INT NOT NULL REFERENCES users(id),
    post_id INT NOT NULL REFERENCES posts(id),
    PRIMARY KEY (user_id, post_id)
);

CREATE TABLE subscriptions (
   following_id INT NOT NULL REFERENCES users(id),
   follower_id INT NOT NULL REFERENCES users(id),
   PRIMARY KEY (following_id, follower_id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    content TEXT NOT NULL,
    sender_id INT NOT NULL REFERENCES users(id),
    recipient_id INT NOT NULL REFERENCES users(id)
);

CREATE INDEX messages_content_ix ON messages(content(255));
