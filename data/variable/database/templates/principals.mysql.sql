CREATE TABLE principals (

    id          INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri         VARCHAR(190) NOT NULL,
    email       VARCHAR(80),
    displayname VARCHAR(80),
    vcardurl    VARCHAR(255),

    UNIQUE(uri)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE groupmembers (

    id           INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principal_id INTEGER UNSIGNED NOT NULL,
    member_id    INTEGER UNSIGNED NOT NULL,

    UNIQUE(principal_id, member_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
