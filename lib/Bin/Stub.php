<?php

namespace Sabre\Katana\Bin;

use Sabre\Katana\Stub\Zip;
use Sabre\Katana\Stub\Phar;
use Sabre\Katana\Protocol;
use Sabre\Katana\Exception;
use Hoa\Console;
use Hoa\File\Finder;
use PharException;

/**
 * Stub the application.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Stub extends AbstractCommand
{

    /**
     * Format: ZIP.
     *
     * @const int
     */
    const FORMAT_ZIP  = 1;
    /**
     * Format: PHAR.
     *
     * @const int
     */
    const FORMAT_PHAR = 2;

    protected $options = [
        ['zip',  Console\GetOption::NO_ARGUMENT, 'z'],
        ['phar', Console\GetOption::NO_ARGUMENT, 'p'],
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
        $format = 0;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);
                    break;

                case 'z':
                    $format = static::FORMAT_ZIP;
                    break;

                case 'p':
                    $format = static::FORMAT_PHAR;
                    break;

                case 'h':
                case '?':
                default:
                    return $this->usage();
                    break;

            }
        }

        $archiveName = null;
        $finder      = new Finder();
        $finder
            ->files()
            ->in(SABRE_KATANA_PREFIX)
            ->notIn('/^\.git$/');

        if (0 === $format) {
            return $this->usage();
        } elseif (static::FORMAT_ZIP === $format) {

            $pathName = 'katana.zip';
            $finder->notIn('/^' . preg_quote($pathName, '/') . '$/');

            $archiveName  =
            $pharPathname = Protocol::realPath('katana://data/share/' . $pathName, false);

            if (true === file_exists($pharPathname)) {
                unlink($pharPathname);
            }

            $zip = new Zip($pharPathname);
            $zip->buildFromIterator($finder, SABRE_KATANA_PREFIX);

        } elseif (static::FORMAT_PHAR === $format) {

            if (false === Phar::canWrite()) {
                throw new Exception\Console(
                    'Cannot create the PHAR. ' .
                    'Retry with `php -d phar.readonly=0 ' .
                    $_SERVER['argv'][0] . ' stub --phar'
                );
            }

            $pathName = 'katana.phar';
            $finder->notIn('/^' . preg_quote($pathName, '/') . '$/');

            $archiveName  =
            $pharPathname = Protocol::realPath('katana://data/share/' . $pathName, false);

            if (true === file_exists($pharPathname)) {
                unlink($pharPathname);
            }

            $phar = new Phar($pharPathname);
            $phar->buildFromIterator($finder, SABRE_KATANA_PREFIX);
            $phar->setStub($phar->getStubCode());

        }

        echo $archiveName, "\n";

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
            'Usage  : stub <options>', "\n",
            'Options:', "\n",
            $this->makeUsageOptionsList([
                'z'    => 'Produce a ZIP.',
                'p'    => 'Produce an executable PHAR.',
                'help' => 'This help.'
            ]);
    }
}
