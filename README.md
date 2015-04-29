# ![K (sabre/katana's logo)](public/static/image/katana_logo_full.png)

sabre/katana is a [CalDAV], [CardDAV] and [WebDAV] ready-to-use server on top of
[sabre/dav]. It means you can have your own **calendars**, **tasks** and
**contacts** server in a minute, robust, safe and secure.

## Features

When sabre/katana is installed, you can:

  * manage **users** with the administration panel,
  * create, update and delete **calendars** and **tasks** with any
    [CalDAV]-compatible client,
  * create, update and delete **contacts** with any [CardDAV]-compatible
    client.

More than 35 RFCs supported. See [the exhaustive list of all supported
standars][sabre_standards]. This includes: vCard 4.0, iCalendar 2.0, jCal,
jCard, iTip, iMip, ACL etc.

[WebDAV] support is coming soon.

sabre/katana is powered by [sabre/dav], an open source [WebDAV], [CardDAV]
and [CalDAV] technology, trusted by the likes of [Atmail], [Box], [fruux]
and [ownCloud].

## Install

If you downloaded sabre/katana as an archive, skip the pre-requisites.

### Pre-requisites

To grab dependencies of the project, make sure you have [Composer], [Bower] and
[NPM] installed, and then run:

```sh
$ make install
```

(Note: You can run `make clean` to clean extra files needed for the
installation).

Then, to install sabre/katana, you have two options.

### In your browser

You need to start an HTTP server; example with the PHP built-in server:

```sh
$ php -S 127.0.0.1:8888 -t public public/.webserver.php
```

If you use another HTTP server, take a look at
`data/etc/configuration/http_servers/`, you will find more configuration files.

Then open
[`http://127.0.0.1:8888/install.php`](http://127.0.0.1:8888/install.php) in your
browser, you will be redirected to the installation page.

### In your terminal

You need to execute the following command:

 ```sh
 $ bin/katana install
 ```

## Update

To update sabre/katana, you have two options.

### In your browser

So far, only a message will be prompt, indicating how to update manually.
We are working on automatic update in the browser.

### In your terminal

  1. First solution is **manual** but more common. It requires a ZIP archive.
     Download the latest versions with the following command:

     ```sh
     $ bin/katana update --fetch-zip
     ```

     You will find the archives in the `data/share/update/` directory. To
     finally update sabre/katana, simply run:

     ```sh
     $ unzip -u data/share/update/katana_vx.y.z.zip -d .
     ```

  2. Second solution is **automatic** but less common. It requires a [PHAR]
     archive. Download the latest versions with the following command:

     ```sh
     $ bin/katana update --fetch
     ```

     You will also find the archives in the `data/share/update/` directory. To
     finally update sabre/katana, simply run:

     ```sh
     $ bin/katana update --apply data/share/update/katana_vx.y.z.phar
     ```

     The PHAR is executable. For instance:

     ```sh
     $ php data/share/update/katana_vx.y.z.phar --signature
     ```

     or

     ```sh
     $ php data/share/update/katana_vx.y.z.phar --metadata
     ```

     will respectively print the signature and the metadata of this version. Use
     `-h`, `-?` or `--help` to get help.

## Raw backup

So far, it is possible to only create a backup in your terminal, by using the
following commands:

```sh
$ bin/katana stub --zip
```

to create a ZIP archive, or

```sh
$ bin/katana stub --phar
```

to create an executable PHAR archive.

**⚠️ Warning**: The current command does not backup MySQL database.

## Supported technologies

So far, sabre/katana can be installed with [SQLite] or [MySQL]. It works in all
major browsers, except IE9 (we are working on it).

## Build status

| branch | status |
| ------ | ------ |
| master | [![Build Status](https://travis-ci.org/fruux/sabre-katana.png?branch=master)](https://travis-ci.org/fruux/sabre-katana) |

# Questions?

Head over to the [sabre/dav mailinglist][mailinglist], or you can also just
[open a ticket on GitHub][issues].

# Made at fruux

sabre/katana is being developed by [fruux]. Drop us a line for commercial
services or enterprise support.

[Atmail]: https://www.atmail.com/
[Bower]: http://bower.io/
[Box]: https://www.box.com/blog/in-search-of-an-open-source-webdav-solution/
[CalDAV]: https://en.wikipedia.org/wiki/CalDAV
[CardDAV]: https://en.wikipedia.org/wiki/CardDAV
[Composer]: http://getcomposer.org/
[MySQL]: http://mysql.com/
[NPM]: http://npmjs.org/
[PHAR]: http://php.net/phar
[SQLite]: http://sqlite.org/
[WebDAV]: https://en.wikipedia.org/wiki/WebDAV
[fruux]: https://fruux.com/
[issues]: https://github.com/fruux/sabre-katana/issues/
[mailinglist]: http://groups.google.com/group/sabredav-discuss
[ownCloud]: http://owncloud.org/
[sabre/dav]: http://sabre.io/
[sabre_standards]: http://sabre.io/dav/standards-support/
