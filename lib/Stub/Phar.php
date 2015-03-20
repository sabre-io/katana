<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2015  fruux GmbH (https://fruux.com/)
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

namespace Sabre\Katana\Stub;

use Hoa\File;
use Phar as PHPPhar;

/**
 * Create a PHAR archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Phar extends PHPPhar
{
    /**
     * Open a PHAR archive. If it does not exist, attempt to create it.
     *
     * @param  string  $filename    Filename (see original documentation).
     * @param  int     $flags       Flags (see original documentation).
     * @param  string  $alias       Alias (see original documentation).
     * @return void
     */
    public function __construct($filename, $flags = null, $alias = null)
    {
        if(null !== $alias) {
            parent::__construct($filename, $flags, $alias);
        } elseif (null !== $flags) {
            parent::__construct($filename, $flags);
        } else {
            parent::__construct($filename);
        }

        $this->setSignatureAlgorithm(static::SHA1);
        $this->setMetadata([
            'author'    => 'fruux GmbH (https://fruux.com/)',
            'license'   => 'Modified BSD License (http://sabre.io/license/)',
            'copyright' => 'Copyright (C) 2015 fruux GmbH (https://fruux.com/)',
            'datetime'  => date('c')
        ]);

        return;
    }

    /**
     * Get the standard stub.
     *
     * @return string
     */
    public function getStubCode()
    {
        $stub = new File\Read(__FILE__);
        $stub->seek(__COMPILER_HALT_OFFSET__);

        return trim($stub->read(1 << 20)) . "\n" . '__HALT_COMPILER();';
    }
}

__HALT_COMPILER();
#!/usr/bin/env php
<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2015  fruux GmbH (https://fruux.com/)
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

Phar::mapPhar('katana.phar');

require 'phar://katana.phar/bootstrap.php';

use Hoa\Router;
use Hoa\Console;
use Hoa\Iterator;

/**
 * Executable stub of the PHAR.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */

$pharPathname = __FILE__;
$phar         = new Phar(
    $pharPathname,
    Phar::KEY_AS_FILENAME |
    Phar::CURRENT_AS_FILEINFO
);
$parser = new Console\Parser();
$parser->parse(Router\Cli::getURI());
$options = new Console\GetOption(
    [
        ['extract',   Console\GetOption::REQUIRED_ARGUMENT, 'e'],
        ['overwrite', Console\GetOption::NO_ARGUMENT,       'o'],
        ['metadata',  Console\GetOption::NO_ARGUMENT,       'm'],
        ['signature', Console\GetOption::NO_ARGUMENT,       's'],
        ['list',      Console\GetOption::NO_ARGUMENT,       'l'],
        ['help',      Console\GetOption::NO_ARGUMENT,       'h'],
        ['help',      Console\GetOption::NO_ARGUMENT,       '?']
    ],
    $parser
);

function usage()
{
    echo
        'Usage  : ', $_SERVER['argv'][0], ' <options>', "\n",
        'Options:', "\n",
        '    -e, --extract=  : Extract the archive into a specific directory.', "\n",
        '    -o, --overwrite : Overwrite existing files while extracting.', "\n",
        '    -m, --metadata  : Print metadata of the archive.', "\n",
        '    -s, --signature : Print the signature of the archive.', "\n",
        '    -l, --list      : List content of the archive.', "\n",
        '    -h, --help      : This help.', "\n",
        '    -?, --help      : This help.', "\n";

    return 0;
}

$overwrite = false;
$extractTo = null;

while (false !== $c = $options->getOption($v)) {
    switch ($c) {

        case '__ambiguous':
            $options->resolveOptionAmbiguity($v);
            break;

        case 'e':
            $extractTo = $v;
            break;

        case 'o':
            $overwrite = $v;
            break;

        case 'm':
            $metadata = $phar->getMetadata();
            $max      = 0;

            foreach ($metadata as $key => $value) {
                $max < $l = strlen($key) and $max = $l;
            }

            foreach ($metadata as $key => $value) {
                echo
                    sprintf(
                        '%-' . $max . 's ' . "\t" . '%s',
                        $key,
                        str_replace("\n", ' ', $value)
                    ),
                    "\n";
            }
            exit(0);
            break;

        case 's':
            echo $phar->getSignature()['hash'], "\n";
            exit(0);
            break;

        case 'l':
            $prefixLength = strlen('phar://' . $pharPathname);
            $iterator     = new Iterator\Recursive\Iterator($phar);
            foreach ($iterator as $value) {
                echo '/' . $iterator->getSubPathName(), "\n";
            }
            exit(0);
            break;

        case 'h':
        case '?':
        default:
            return usage();
            break;

    }
}

if (null !== $extractTo) {

    if (false === file_exists($extractTo)) {
        mkdir($extractTo);
    }

    if (false === file_exists($extractTo)) {

        echo
            $extractTo, ' cannot be created.', "\n",
            'Extraction abort.', "\n";
        exit(1);

    }

    try {
        $code = false === $phar->extractTo($extractTo, null, $overwrite);
    }  catch (PharException $exception) {

        echo $exception->getMessage();
        $code = 1;

    }

    exit($code);

} else {
    exit(usage());
}
