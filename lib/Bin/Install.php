<?php

namespace Sabre\Katana\Bin;

use Sabre\Katana\Server\Installer;
use Hoa\Console;

/**
 * Install page.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Install extends AbstractCommand
{
    protected $options = [
        ['help', Console\GetOption::NO_ARGUMENT, 'h'],
        ['help', Console\GetOption::NO_ARGUMENT, '?']
    ];

    /**
     * Main method.
     *
     * @return int
     */
    public function main()
    {
        while (false !== $c = $this->getOption($v)) {
            switch ($c) {

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);
                    break;

                case 'h':
                case '?':
                default:
                    return $this->usage();
                    break;

            }
        }

        if (true === Installer::isInstalled()) {

            echo 'The application is already installed.', "\n";
            return 1;

        }

        return;
    }

    /**
     * Print the usage.
     *
     * @return void
     */
    public function usage()
    {
        echo
            'Usage  : install <options>', "\n",
            'Options:', "\n",
            $this->makeUsageOptionsList([
                'help' => 'This help.'
            ]);
    }
}
