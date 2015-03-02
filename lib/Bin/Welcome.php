<?php

namespace Sabre\Katana\Bin;

use Hoa\Console;

/**
 * Welcome page.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Welcome extends AbstractCommand
{
    const LOGO = <<<LOGO
 _  __     _
| |/ /__ _| |_ __ _ _ __   __ _
| ' // _` | __/ _` | '_ \ / _` |
| . \ (_| | || (_| | | | | (_| |
|_|\_\__,_|\__\__,_|_| |_|\__,_|

LOGO;

    protected $options = [
        ['no-verbose', Console\GetOption::NO_ARGUMENT, 'V'],
        ['help',       Console\GetOption::NO_ARGUMENT, 'h'],
        ['help',       Console\GetOption::NO_ARGUMENT, '?']
    ];

    protected $commands = [
        'welcome',
        'install'
    ];

    /**
     * Main method.
     *
     * @return int
     */
    public function main()
    {
        $verbose = Console::isDirect(STDOUT);

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {

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
            return;

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
            'Usage  : welcome <options>', "\n",
            'Options:', "\n",
            $this->makeUsageOptionsList([
                'V'    => 'Be as less verbose as possible.',
                'help' => 'This help.'
            ]);
    }
}
