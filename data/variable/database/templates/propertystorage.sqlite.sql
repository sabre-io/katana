CREATE TABLE propertystorage (

    id    INTEGER PRIMARY KEY ASC,
    path  TEXT,
    name  TEXT,
    value TEXT

);

CREATE UNIQUE INDEX path_property
    ON propertystorage (path, name);
