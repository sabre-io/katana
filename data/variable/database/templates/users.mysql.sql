CREATE TABLE users (

    id       INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),
    digesta1 VARCHAR(32),

    UNIQUE(username)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT
    INTO users (username, digesta1)
    VALUES ('admin', '87fd274b7b6c01e48d7c2f965da8ddf7');
