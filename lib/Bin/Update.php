<?php

namespace Sabre\Katana\Bin;

use Sabre\Katana\Exception;
use Sabre\Katana\Protocol;
use Hoa\Core;
use Hoa\Console;
use Hoa\Console\Cursor;
use Hoa\Console\Window;
use Hoa\File;

/**
 * Update the application.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Update extends AbstractCommand
{
    /**
     * Operation: fetch.
     *
     * @const int
     */
    const OPERATION_FETCH = 1;

    /**
     * Operation: apply.
     *
     * @const int
     */
    const OPERATION_APPLY = 2;

    /**
     * Format: PHAR.
     *
     * @const int
     */
    const FORMAT_PHAR     = 4;

    /**
     * Format: ZIP.
     *
     * @const int
     */
    const FORMAT_ZIP      = 8;

    protected $options = [
        ['fetch',     Console\GetOption::NO_ARGUMENT,       'f'],
        ['fetch-zip', Console\GetOption::NO_ARGUMENT,       'z'],
        ['apply',     Console\GetOption::REQUIRED_ARGUMENT, 'a'],
        ['help',      Console\GetOption::NO_ARGUMENT,       'h'],
        ['help',      Console\GetOption::NO_ARGUMENT,       '?']
    ];

    /**
     * Main method.
     *
     * @return int
     */
    public function main()
    {
        $operation = 0;
        $location  = null;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);
                    break;

                case 'f':
                    $operation = static::OPERATION_FETCH | static::FORMAT_PHAR;
                    break;

                case 'z':
                    $operation = static::OPERATION_FETCH | static::FORMAT_ZIP;
                    break;

                case 'a':
                    $operation = static::OPERATION_APPLY;
                    $location  = $v;
                    break;

                case 'h':
                case '?':
                default:
                    return $this->usage();
                    break;

            }
        }

        if (0 !== (static::OPERATION_FETCH & $operation)) {

            $windowWidth = Window::getSize()['x'];
            $progress    = function($percent) use($windowWidth) {

                Cursor::clear('↔');
                $message  = 'Downloading… ';
                $barWidth = $windowWidth - mb_strlen($message);

                if ($percent <= 0) {
                    $color = '#c74844';
                } elseif ($percent <= 25) {
                    $color = '#cb9a3d';
                } elseif ($percent <= 50) {
                    $color = '#dcb11e';
                } elseif ($percent <= 75) {
                    $color = '#aed633';
                } else {
                    $color = '#54b455';
                }

                echo $message;
                Cursor::colorize('foreground(' . $color . ') background(' . $color . ')');
                echo str_repeat('|', ($percent * $barWidth) / 100);
                Cursor::colorize('normal');

                return;

            };

            $fileIn = new File\Read(
                'http://127.0.0.1:8888/katana.phar',
                File::MODE_READ,
                null,
                true
            );
            $fileOut = new File\Write(
                'katana://data/share/updates/katana.phar'
            );

            echo 'Waiting…', "\n";

            $fileIn->on(
                'connect',
                function() {

                    Cursor::clear('↔');
                    echo 'Downloading… ';
                    return;

                }
            );
            $fileIn->on(
                'progress',
                function(Core\Event\Bucket $bucket)
                use($progress) {

                    static $previousPercent = 0;

                    $data    = $bucket->getData();
                    $current = $data['transferred'];
                    $max     = $data['max'];

                    $percent = ($current * 100) / $max;
                    $delta   = $percent - $previousPercent;

                    if (1 <= $delta) {
                        $previousPercent = $percent;
                        $progress($percent);
                    }

                    return;

                }
            );
            $fileIn->open();
            $fileOut->writeAll($fileIn->readAll());

            echo
                "\n",
                'Fetched at ',
                Protocol::realPath($fileOut->getStreamName()),
                '.',
                "\n";

            return 0;

        } elseif (static::OPERATION_APPLY === $operation) {

            if (false === file_exists($location)) {
                throw new Exception\Console(
                    'Update %s is not found.',
                    0,
                    $location
                );
            }

            $processus = new Console\Processus(
                Core::getPHPBinary(),
                [
                    $location,
                    '--extract' => SABRE_KATANA_PREFIX,
                    '--overwrite'
                ]
            );
            $processus->on('input', function() {
                return false;
            });
            $processus->on('output', function(Core\Event\Bucket $bucket) {
                echo $bucket->getData()['line'], "\n";
            });
            $processus->run();

            if (true === $processus->isSuccessful()) {
                echo 'sabre/katana updated!', "\n";
            } else {
                echo 'Something wrong happened!', "\n";
            }

            return $processus->getExitCode();

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
            'Usage  : update <options>', "\n",
            'Options:', "\n",
            $this->makeUsageOptionsList([
                'f'    => 'Fetch the new updates as PHAR archives.',
                'z'    => 'Fetch the new updates as ZIP archives.',
                'a'    => 'Apply one or many new updates.',
                'help' => 'This help.'
            ]);
    }
}
