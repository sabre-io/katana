CREATE TABLE users (

    id       INTEGER PRIMARY KEY ASC,
    username TEXT,
    digesta1 TEXT,

    UNIQUE(username)

);

INSERT
    INTO users (username, digesta1)
    VALUES ('admin', '87fd274b7b6c01e48d7c2f965da8ddf7');
