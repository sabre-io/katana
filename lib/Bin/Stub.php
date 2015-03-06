<?php

namespace Sabre\Katana\Bin;

use Sabre\Katana\Stub\Zip;
use Sabre\Katana\Stub\Phar;
use Sabre\Katana\Protocol;
use Sabre\Katana\Exception;
use Hoa\Console;
use Hoa\File;
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
                    'Retry with `php -dphar.readonly=0 ' .
                    $_SERVER['argv'][0] . ' stub --phar'
                );
            }

            $pathName = 'katana.phar';
            $finder->notIn('/^' . preg_quote($pathName, '/') . '$/');

            $stub = new File\Read(__FILE__);
            $stub->seek(__COMPILER_HALT_OFFSET__);

            $archiveName  =
            $pharPathname = Protocol::realPath('katana://data/share/' . $pathName, false);

            if (true === file_exists($pharPathname)) {
                unlink($pharPathname);
            }

            $phar = new Phar($pharPathname);
            $phar->buildFromIterator($finder, SABRE_KATANA_PREFIX);
            $phar->setStub(trim($stub->read(1 << 20)) . "\n" . '__HALT_COMPILER();');

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

__HALT_COMPILER();
#!/usr/bin/env php
<?php

Phar::mapPhar('katana.phar');

require 'phar://katana.phar/bootstrap.php';

use Hoa\Router;
use Hoa\Console;
use Hoa\Iterator;

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
            return 0;
            break;

        case 's':
            echo $phar->getSignature()['hash'], "\n";
            return 0;
            break;

        case 'l':
            $prefixLength = strlen('phar://' . $pharPathname);
            $iterator     = new Iterator\Recursive\Iterator($phar);
            foreach ($iterator as $value) {
                echo '/' . $iterator->getSubPathName(), "\n";
            }
            return 0;
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
        return 1;

    }

    try {
        return false === $phar->extractTo($extractTo, null, $overwrite);
    }  catch (PharException $exception) {
        echo $exception->getMessage();
        return 1;

    }

} else {
    return usage();
}
