# ![K (sabre/katana's logo)](../public/static/image/katana_logo_full.png)

## Dependencies

If this directory is **empty**, it means that the dependencies are not installed
yet.

Dependencies are managed by [Composer]. They are declared in the
`composer.json` file at the root of the project.

To install them:

```sh
$ cd ../
$ make install-server
```

### Development dependencies

To be able to run tests or such development tools, simply run:

```sh
$ cd ../
$ make devinstall-server
```

### Optimizing

Composer generates an autoloader, i.e. a component responsible to load every
entities (classes, interfaces, traits, functions etc.) on-demand to avoid I/O.
The current Composer configuration requests to generate an optimized autoloader.
It boosts the execution of the application approximately up to 20%.

[Composer]: http://getcomposer.org/
