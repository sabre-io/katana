# ![K (sabre/katana's logo)](public/static/image/katana_logo_full.png)

sabre/katana is a **contact**, **calendar**, **task list** and **file** server.
Based on world-widely used standards (respectively [CardDAV], [CalDAV] and
[WebDAV]), sabre/katana will sync your data between **any devices**, from
**everywhere**, **every time**. Let's manage users and their data in a minute.
sabre/katana is powered by sabre/dav, an open source technology trusted by the
likes of [Atmail], [Box], [fruux] and [ownCloud].

## Features

Once sabre/katana has been installed, you will be able to manage:

  * Users,
  * Address books,
  * Calendars,
  * Task lists,
  * Files.

More than 35 RFCs are supported. See [the exhaustive list of all supported
standars][sabre_standards]. This includes: vCard 4.0, iCalendar 2.0, jCal,
jCard, iTip, iMip, ACL etc.

Overview of features:

  * WebDAV: Principals, users, HTTP authentification, ACL and synchronization,
  * Address books: VCF export,
  * Calendars: ICS export, scheduling (iTIP and iMIP, respectively invite
    attendees that are registered on your server or not),
  * Task lists: ICS export,
  * Files: Home collection, public directory, locks.

### Screenshots

Easy installation through a wizzard:

![Installation in the browser](https://farm8.staticflickr.com/7765/17197365573_471d88c2d2_z.jpg?1)

You are also able to install in CLI:

![Installation in the terminal](https://farm6.staticflickr.com/5337/17818002185_c1762109a7_z.jpg?1)

The login page, first step to manage your sabre/katana server:

![Login page](https://farm6.staticflickr.com/5348/17791399666_844d2b2248_z.jpg?2)

List of users on the left:

![List of users](https://farm9.staticflickr.com/8793/17195211304_018fb26eb6_z.jpg?3)

When creating a new user, 1 address book, 2 calendars, 1 task list and 1 home
collection (with a public directory) will be automatically created:

![New user](https://farm6.staticflickr.com/5350/17818186851_6a38c97922_z.jpg?4)

You are able to create, edit, delete or download address books:

![List of address books](https://farm8.staticflickr.com/7689/17629907698_cc1d9ea936_z.jpg?4)

You are also able to create, edit, delete or download calendars and task lists:

![List of calendars](https://farm9.staticflickr.com/8846/17630174780_0126895a26_z.jpg?6)

You can explore the home collection of a user. Notice the public directory,
files put inside are accessible to anyone but no listing is available:

![List of files](https://farm6.staticflickr.com/5334/17818198201_5707937101_z.jpg?6)

Manage your settings easily:

![Settings](https://farm9.staticflickr.com/8842/18628078892_ce96ca45e2_z.jpg?9)

Attendees can be invited to an event even if they are not registered on your
sabre/katana server by automatically sending an email:

![Invite_attendees_by_email](https://farm1.staticflickr.com/360/18010516654_d406c92b50_z.jpg?8)

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
major browsers, except prior versions of IE9.

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
