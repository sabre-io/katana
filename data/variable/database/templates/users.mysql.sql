CREATE TABLE users (

    id           INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username     VARBINARY(50),
    passwordhash VARBINARY(60),

    UNIQUE(username)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
