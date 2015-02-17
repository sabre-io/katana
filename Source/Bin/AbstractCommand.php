<?php

namespace Sabre\Katana\Bin;

use Hoa\Console;

/**
 * Abstract command.
 *
 * @copyright Copyright (C) 2015-2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
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
