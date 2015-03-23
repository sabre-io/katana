# sabre/katana's dependencies

If this directory is **empty**, it means that the dependencies are not installed
yet.

Dependencies are managed by [Composer][0]. They are declared in the
`composer.json` file at the root of the project.

To install them:

```sh
$ composer install --working-dir ../../ --no-dev
```

To update them:

```sh
$ composer update --working-dir ../../ --no-dev
```

## Development dependencies

To be able to run tests or such development tools, simply omit the `--no-dev`
option (or replace it by `--dev`, depending of your Composer version).

We assume Composer is up-to-date. Nevertheless, if Composer needs to be updated,
run:

```sh
$ composer self-update
```

Then, to install all dependencies:

```sh
$ composer install --working-dir ../../
```

To update to all dependencies:

```sh
$ composer update --working-dir ../../
```

## Optimizing

Composer generates an autoloader, i.e. a component responsible to load every
entities (classes, interfaces, traits, functions etc.) on-demand to avoid I/O.
The current Composer configuration requests to generate an optimized autoloader.
It boosts the execution of the application approximately up to 20%.

[0]: http://getcomposer.org/
