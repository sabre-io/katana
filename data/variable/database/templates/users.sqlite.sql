CREATE TABLE users (

    id           INTEGER PRIMARY KEY ASC,
    username     TEXT,
    passwordhash TEXT,

    UNIQUE(username)

);
