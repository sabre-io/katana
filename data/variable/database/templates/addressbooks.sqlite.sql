CREATE TABLE addressbooks (

    id           INTEGER PRIMARY KEY ASC,
    principaluri TEXT,
    displayname  TEXT,
    uri          TEXT,
    description  TEXT,
    synctoken    INTEGER

);

CREATE TABLE cards (

    id            INTEGER PRIMARY KEY ASC,
    addressbookid INTEGER,
    carddata      BLOB,
    uri           TEXT,
    lastmodified  INTEGER,
    etag          TEXT,
    size          INTEGER

);

CREATE TABLE addressbookchanges (

    id            INTEGER PRIMARY KEY ASC,
    uri           TEXT,
    synctoken     INTEGER,
    addressbookid INTEGER,
    operation     INTEGER

);

CREATE INDEX addressbookid_synctoken
    ON addressbookchanges (addressbookid, synctoken);
