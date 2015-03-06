# sabre/katana

sabre/katana is a CalDAV, CardDAV and WebDAV ready-to-use server on top of
[SabreDAV][0].

## Install

If you get sabre/katana through an archive, jump directly to the sub-sections.

Be ensured you have [Composer][1] installed, and then run:

```sh
$ composer install
```

Be ensured you have [Bower][5] installed, and then run:

```sh
$ bower install
```

To install sabre/katana, you have two interfaces.

### In your browser

You need to start an HTTP server; example with the PHP built-in server:

```sh
$ php -S 127.0.0.1:8888 -t public public/.webserver.php
```

If you use another HTTP server, take a look at
`data/etc/configuration/http_servers/`, you will find more configuration files.

Then open [`127.0.0.1:8888`](http://127.0.0.1:8888) in your browser, you will be
redirected to the installation page.

### In your terminal

You need to execute the following command:

 ```sh
 $ bin/katana install
 ```

 If you are using Windows or you don't want a fancy interface, try:

 ```sh
 $ bin/katana install --no-verbose
 ```

### Supported databases

So far, sabre/katana can be installed with [SQLite][6] or [MySQL][7].

## Update

To update sabre/katana, you have two interfaces.

### In your browser

**under development** (sorry, we are working hard on it).

### In your terminal

  1. First solution is manual but more common. It requires a ZIP archive.
     Download the latest versions with the following command:

     ```sh
     $ bin/katana update --fetch-zip
     ```

     You will find the archives in the `data/share/update/` directory. To
     finally update sabre/katana, simply run:

     ```sh
     $ unzip -u data/share/katana_vx.y.z.zip -d .
     ```

  2. Second solution is automatic but less common. It requires a [PHAR][8]
     archive. Download the latest versions with the following command:

     ```sh
     $ bin/katana update --fetch
     ```

     You will also find the archives in the `data/share/update/` directory. To
     finally update sabre/katana, simply run:

     ```sh
     $ bin/katana update --apply data/share/katana_vx.y.z.phar
     ```

     The PHAR is executable. For instance:

     ```sh
     $ php data/share/katana_vx.y.z.phar --signature
     ```

     or

     ```sh
     $ php data/share/katana_vx.y.z.phar --metadata
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

## Build status

| branch | status |
| ------ | ------ |
| master | [![Build Status](https://travis-ci.org/fruux/sabre-katana.png?branch=master)](https://travis-ci.org/fruux/sabre-katana) |


# Questions?

Head over to the [sabre/dav mailinglist][2], or you can also just open a ticket
on [GitHub][3].


# Made at fruux

This library is being developed by [fruux][4]. Drop us a line for commercial
services or enterprise support.

[0]: http://sabre.io/
[1]: http://getcomposer.org/
[2]: http://groups.google.com/group/sabredav-discuss
[3]: https://github.com/fruux/sabre-katana/issues/
[4]: https://fruux.com/
[5]: http://bower.io/
[6]: http://sqlite.org/
[7]: http://mysql.com/
[8]: http://php.net/phar
