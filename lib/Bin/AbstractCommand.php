<?php

namespace Sabre\Katana\Bin;

use Hoa\Console;

/**
 * Abstract command.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class AbstractCommand extends Console\Dispatcher\Kit
{
    /**
     * Main method.
     *
     * @return int
     */
    abstract function main();

    /**
     * Print the usage.
     *
     * @return void
     */
    abstract function usage();
}
