CREATE TABLE locks (

    id      INTEGER PRIMARY KEY ASC,
    owner   TEXT,
    timeout INTEGER,
    created INTEGER,
    token   TEXT,
    scope   INTEGER,
    depth   INTEGER,
    uri     TEXT

);
