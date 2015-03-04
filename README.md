# sabre/katana

sabre/katana is a CalDAV, CardDAV and WebDAV ready-to-use server on top of
[SabreDAV][0].

## Installation

Be ensured you have [Composer][1] installed, and then run:

```sh
$ composer install
```

Be ensured you have [Bower][5] installed, and then run:

```sh
$ bower install
```

To install sabre/katana, you have two interfaces:

  1. In your browser, you need to start an HTTP server; example with the PHP
     built-in server:

     ```sh
     $ php -S 127.0.0.1:8888 -t public public/.webserver.php
     ```

     If you use another HTTP servers, take a look at
     `data/etc/configuration/http_servers/`, you will find more configuration
     files.

     Then open [`127.0.0.1:8888`](http://127.0.0.1:8888) in your browser, you
     will be redirected to the installation page.

  2. In your terminal:

     ```sh
     $ bin/katana install
     ```

     If you are using Windows or you don't want a fancy interface, try:

     ```sh
     $ bin/katana install --no-verbose
     ```

### Supported databases

So far, sabre/katana can be installed with [SQLite][6] or [MySQL][7].

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
