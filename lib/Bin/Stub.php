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

use Sabre\Katana\Stub\Zip;
use Sabre\Katana\Stub\Phar;
use Sabre\Katana\Protocol;
use Sabre\Katana\Exception;
use Hoa\Console;
use Hoa\File\Finder;

/**
 * Stub the application.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Stub extends AbstractCommand {
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
    function main() {
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
    }

    /**
     * Print the usage.
     *
     * @return void
     */
    function usage() {
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
