# sabre/katana's tests

## Unit testing

Unit tests are written and executed with [atoum](http://atoum.org/). They are
located in the `Unit/` directory under the `Sabre\Katana\Test\Unit` namespace.

### Execution

If `require-dev`s in the `composer.json` file are correctly installed, one
should do (from this directory):

```sh
$ ../bin/atoum
```

That's all folks!

### Naming and style conventions

A test case is composed of:
  * a test preamble or a precondition,
  * an invocation of the SUT (System Under Test),
  * a postcondition.

A test case is represented by the following structure:

```php
$this
    // preamble/precondition
    ->given(
        …,
        …,
        …
    )
    // invocation
    ->when(…)
    // postcondition
    ->then
        ->…
```

We call “SUT” the System Under Test. However, it can be ambiguous in some cases.
Thus, we use “CUT” —standing for Class Under Test— and “LUT” —standing for
Library Under Test— as aliases.

### Differences with a bare atoum

  * A class represents a test suite, and therefore extends the
    `Sabre\Katana\Test\Unit\Suite` class in the case of unit tests.
  * A method represents a test case, and therefore is prefixed by `case`.
  * Tests are decorrelated from the SUT.
  * Mocking system uses the root namespace `\Mouck` and not `\Mock` in order to
    avoid conflicts with existing mock classes in the `Mock/` directory.

### Tags

Tags are attached to test suites or test cases in order to classify them in
another way. Here is the list of the existing tags with their respective
description:

| Tags               | Description                              |
|--------------------|------------------------------------------|
| `configuration`    | About the configuration                  |
| `database`         | About the database (whatever the driver) |
| `sqlite`           | About the SQLite driver of the database  |
| `mysql`            | About the MySQL driver of the database   |
| `protocol`         | About the `katana://` protocol           |
| `installation`     | About the installation                   |
| `http`             | About code using HTTP                    |
| `authentification` | About the authentification               |
| `administration`   | About the administration                 |
| `stub`             | About the stub                           |
| `phar`             | About the PHAR archive                   |

To run all the tests about the installation, we can use the following command
line:

```sh
$ ../bin/atoum --filter '"installation" in tags'
```

To run all the tests about the installation and the SQLite driver:

```sh
$ ../bin/atoum --filter '"installation" in tags and "sqlite" in tags'
```

To run all the tests about the configuration, except tests about the
installation:

```sh
$ ../bin/atoum --filter '"configuration" in tags and not("installation" in tags)'
```

**Tips**: The `--filter` option is more powerful, for instance to run all the
test about the installation and the SQLite driver in the
`Sabre\Katana\Test\Unit\Server\Installer` class:

```sh
$ ../bin/atoum --filter 'class = "Sabre\Katana\Test\Unit\Server\Installer" and "installation" in tags and "sqlite" in tags'
```

Note we used `and` to refine constraints, i.e. exclude some tests. We can use
`or` to add more tests.
