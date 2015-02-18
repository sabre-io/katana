CREATE TABLE calendarobjects (

    id             INTEGER PRIMARY KEY ASC,
    calendardata   BLOB,
    uri            TEXT,
    calendarid     INTEGER,
    lastmodified   INTEGER,
    etag           TEXT,
    size           INTEGER,
    componenttype  TEXT,
    firstoccurence INTEGER,
    lastoccurence  INTEGER,
    uid            TEXT

);

CREATE TABLE calendars (

    id            INTEGER PRIMARY KEY ASC,
    principaluri  TEXT,
    displayname   TEXT,
    uri           TEXT,
    synctoken     INTEGER,
    description   TEXT,
    calendarorder INTEGER,
    calendarcolor TEXT,
    timezone      TEXT,
    components    TEXT,
    transparent   BOOL

);

CREATE TABLE calendarchanges (

    id         INTEGER PRIMARY KEY ASC,
    uri        TEXT,
    synctoken  INTEGER,
    calendarid INTEGER,
    operation  INTEGER

);

CREATE INDEX calendarid_synctoken
    ON calendarchanges (calendarid, synctoken);

CREATE TABLE calendarsubscriptions (

    id               INTEGER PRIMARY KEY ASC,
    uri              TEXT,
    principaluri     TEXT,
    source           TEXT,
    displayname      TEXT,
    refreshrate      TEXT,
    calendarorder    INTEGER,
    calendarcolor    TEXT,
    striptodos       BOOL,
    stripalarms      BOOL,
    stripattachments BOOL,
    lastmodified     INT

);

CREATE TABLE schedulingobjects (

    id             INTEGER PRIMARY KEY ASC,
    principaluri   TEXT,
    calendardata   BLOB,
    uri            TEXT,
    lastmodified   INTEGER,
    etag           TEXT,
    size           INTEGER

);

CREATE INDEX principaluri_uri
    ON calendarsubscriptions (principaluri, uri);
