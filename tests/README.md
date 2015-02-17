# sabre/katana's tests

## Unit testing

Unit tests are written and executed with [atoum](http://atoum.org/). They are
located in the `Unit/` directory under the `Sabre\Katana\Test\Unit` namespace.

### Execution

If `require-dev`s in the `composer.json` file are correctly installed, one
should do (from this directory):

```sh
$ ../vendor/bin/atoum
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

### Differences with a bare atoum

  * A class represents a test suite, and therefore extends the
    `Sabre\Katana\Test\Unit\Suite` class in the case of unit tests.
  * A method represents a test case, and therefore is prefixed by `case`.
  * Tests are decorrelated from the SUT.
  * Mocking system uses the root namespace `\Mouck` and not `\Mock` in order to
    avoid conflicts with existing mock classes in the `Mock/` directory.
