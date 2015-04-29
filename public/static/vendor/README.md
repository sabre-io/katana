# ![K (sabre/katana's logo)](../../../public/static/image/katana_logo_full.png)

## Dependencies

If this directory is **empty**, it means that the dependencies are not installed
yet.

Dependencies are managed by [Bower] and [NPM]. They are declared in the
`bower.json` and `package.json` files at the root of the project.

To install them:

```sh
$ cd ../../../
$ make install-client
$ make build-client
```

## Development dependencies

To be able to run tests or such development tools, simply run:

```sh
$ cd ../../../
$ make devinstall-client
$ make build-client
```

[Bower]: http://bower.io/
[NPM]: http://npmjs.org/
