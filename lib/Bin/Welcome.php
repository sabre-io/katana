<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2015 fruux GmbH (https://fruux.com/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Sabre\Katana\Bin;

use Hoa\Console;

/**
 * Welcome page.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Welcome extends AbstractCommand {

    /**
     * Logo.
     *
     * @const string
     */
    const LOGO = <<<LOGO
 _  __     _
| |/ /__ _| |_ __ _ _ __   __ _
| ' // _` | __/ _` | '_ \ / _` |
| . \ (_| | || (_| | | | | (_| |
|_|\_\__,_|\__\__,_|_| |_|\__,_|

LOGO;

    protected $options = [
        ['prefix',     Console\GetOption::NO_ARGUMENT, 'p'],
        ['version',    Console\GetOption::NO_ARGUMENT, 'v'],
        ['no-verbose', Console\GetOption::NO_ARGUMENT, 'V'],
        ['help',       Console\GetOption::NO_ARGUMENT, 'h'],
        ['help',       Console\GetOption::NO_ARGUMENT, '?']
    ];

    protected $commands = [
        'welcome',
        'install',
        'stub',
        'update'
    ];

    /**
     * Main method.
     *
     * @return int
     */
    function main() {

        $prefix  = SABRE_KATANA_PREFIX;
        $verbose = Console::isDirect(STDOUT);

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {

                case 'p':
                    echo $prefix, "\n";
                    return 0;
                    break;

                case 'v':
                    echo SABRE_KATANA_VERSION, "\n";
                    return 0;
                    break;

                case 'V':
                    $verbose = !$v;
                    break;

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

        if (false === $verbose) {

            echo implode("\n", $this->commands);

        }

        Console\Cursor::colorize('foreground(yellow)');
        echo static::LOGO;
        Console\Cursor::colorize('normal');

        echo
            "\n\n",
            'Just type:', "\n\n",
            '    $ katana <command> <options>', "\n\n",
            'where <command> is:', "\n\n",
            '    * ',
            implode(',' . "\n" . '    * ', $this->commands),
            '.', "\n\n",
            '<options> always contains -h, -? and --help to get the usage ' .
            'of the command.', "\n";

    }

    /**
     * Print the usage.
     *
     * @return void
     */
    function usage() {

        echo
            'Usage  : welcome <options>', "\n",
            'Options:', "\n",
            $this->makeUsageOptionsList([
                'p'    => 'Print the prefix, i.e. root of the application.',
                'v'    => 'Print the current version.',
                'V'    => 'Be as less verbose as possible.',
                'help' => 'This help.'
            ]);
    }
}
