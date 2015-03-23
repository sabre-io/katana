# sabre/katana's dependencies

If this directory is **empty**, it means that the dependencies are not installed
yet.

Dependencies are managed by [Bower][0]. They are declared in the `bower.json`
file at the root of the project.

To install them:

```sh
$ bower install --config.cwd=../../../ --production
```

To update them:

```sh
$ bower update --config.cwd=../../../ --production
```

## Development dependencies

To be able to run tests or such development tools, simply omit the
`--production` option. Then, to install all dependencies:

```sh
$ bower install --config.cwd=../../../
```

To update to all dependencies:

```sh
$ bower update --config.cwd=../../../
```

[0]: http://bower.io/
