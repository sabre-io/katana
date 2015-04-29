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
namespace Sabre\Katana\Test\Integration\Stub;

use Sabre\Katana\Test\Integration\Suite;
use Sabre\Katana\Stub\Phar as CUT;
use Sabre\Katana\Protocol;
use Hoa\Core;
use Hoa\Console\Processus;
use Hoa\File\Finder;

/**
 * Test suite of the PHAR archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 *
 * @tags stub phar
 */
class Phar extends Suite
{
    function case_stub_list()
    {
        $this
            ->given($phar = $this->getPhar($pharName))
            ->when($result = $this->execute($pharName, '--list'))
            ->then
                ->array(explode("\n", $result))
                    ->containsValues([
                        '/bootstrap.php',
                        '/vendor/autoload.php'
                    ]);
    }

    function case_stub_metadata()
    {
        $this
            ->given($phar = $this->getPhar($pharName))
            ->when($result = $this->execute($pharName, '--metadata'))
            ->then
                ->string($result)
                    ->isNotEmpty();
    }

    function case_stub_signature()
    {
        $this
            ->given($phar = $this->getPhar($pharName))
            ->when($result = $this->execute($pharName, '--signature'))
            ->then
                ->string($result)
                    ->isEqualTo($phar->getSignature()['hash']);
    }

    function case_stub_extract()
    {
        $this
            ->given(
                $phar            = $this->getPhar($pharName),
                $directoryName   = $this->sample($this->realdom->regex('/\w{40,}/')),
                $outputDirectory = $this->helper->temporaryDirectory($directoryName)
            )
            ->when($this->execute($pharName, '--extract ' . $outputDirectory))
            ->then
                ->boolean(file_exists($outputDirectory . DS . 'bootstrap.php'))
                    ->isTrue()
                ->boolean(file_exists($outputDirectory . DS . 'vendor' . DS . 'autoload.php'))
                    ->isTrue();
    }

    function case_stub_extract_overwrite()
    {
        $this
            ->given(
                $phar            = $this->getPhar($pharName),
                $directoryName   = $this->sample($this->realdom->regex('/\w{40,}/')),
                $outputDirectory = $this->helper->temporaryDirectory($directoryName),
                $collimator      = $outputDirectory . DS . 'bootstrap.php'
            )
            ->when(
                $this->execute(
                    $pharName,
                    '--extract ' . $outputDirectory
                )
            )
            ->then
                ->boolean(file_exists($collimator))
                    ->isTrue()
                ->boolean(file_exists($outputDirectory . DS . 'vendor' . DS . 'autoload.php'))
                    ->isTrue()

            ->when(
                file_put_contents($collimator, 'foo'),
                $result = $this->execute(
                    $pharName,
                    '--extract ' . $outputDirectory
                )
            )
            ->then
                ->string($result)
                    ->matches('/path already exists/')
                ->string(file_get_contents($collimator))
                    ->isEqualTo('foo')

            ->when(
                $this->execute(
                    $pharName,
                    '--extract ' . $outputDirectory . ' ' .
                    '--overwrite'
                )
            )
            ->then
                ->string(file_get_contents($collimator))
                    ->isNotEqualTo('foo');
    }

    protected function getPhar(&$pharName)
    {
        $finder = new Finder();
        $finder
            ->files()
            // We need to get the real path because of the PHAR API…
            ->in(Protocol::realPath('katana://data/lib/composer'))
            ->in(Protocol::realPath('katana://data/lib/hoa/core'))
            ->in(Protocol::realPath('katana://data/lib/hoa/console'))
            ->in(Protocol::realPath('katana://data/lib/hoa/iterator'))
            ->in(Protocol::realPath('katana://data/lib/hoa/router'))
            ->in(Protocol::realPath('katana://data/lib/sabre/uri'))
            ->in(Protocol::realPath('katana://data/lib/ircmaxell/password-compat'))
            ->name('/\.php$/')
            ->notIn('/^\.git$/');

        $pharName = $this->helper->temporaryFile('.phar');
        $phar     = new CUT($pharName);
        $phar->buildFromIterator($finder, SABRE_KATANA_PREFIX);
        $phar['bootstrap.php']       = '<?php require \'vendor/autoload.php\';';
        $phar['vendor/autoload.php'] = file_get_contents('katana://data/lib/autoload.php');
        $phar->setStub($phar->getStubCode());

        return $phar;
    }

    protected function execute($pharName, $options)
    {
        return Processus::execute(
            $this->getPhpPath() .
            ' -d phar.readonly=1 ' .
            $pharName . ' ' .
            $options
        );
    }
}
