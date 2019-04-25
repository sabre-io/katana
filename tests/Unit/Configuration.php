<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2016 fruux GmbH (https://fruux.com/)
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

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Configuration as CUT;

/**
 * Test suite of the configuration component.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 *
 * @tags configuration
 */
class Configuration extends Suite {

    function case_file_not_found() {

        $this
            ->exception(function() {
                new CUT('/foo/42');
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    function case_invalid_json() {

        $this
            ->given(
                $file = $this->helper->configuration('configuration.json'),
                file_put_contents($file, 'x')
            )
            ->exception(function() use ($file) {
                new CUT($file);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    function case_get_filename() {

        $this
            ->given(
                $file = $this->helper->configuration(
                    'configuration.json',
                    []
                ),
                $configuration = new CUT($file)
            )
            ->when($result = $configuration->getFilename())
            ->then
                ->string($result)
                    ->isEqualTo($file);
    }

    function case_isset() {

        $this
            ->given(
                $configuration = new CUT(
                    $this->helper->configuration(
                        'configuration.json',
                        ['a' => 42]
                    )
                )
            )
            ->when($result = isset($configuration->a))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = isset($configuration->z))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    function case_get() {

        $this
            ->given(
                $configuration = new CUT(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'a' => 42,
                            'b' => true,
                            'c' => ['foo' => 'bar']
                        ]
                    )
                )
            )
            ->when($result = $configuration->a)
            ->then
                ->integer($result)
                    ->isEqualTo(42)

            ->when($result = $configuration->b)
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $configuration->c->foo)
            ->then
                ->string($result)
                    ->isEqualTo('bar');
    }

    function case_set() {

        $this
            ->given(
                $configuration = new CUT(
                    $this->helper->configuration(
                        'configuration.json',
                        ['a' => 42]
                    )
                )
            )
            ->when($result = $configuration->a)
            ->then
                ->integer($result)
                    ->isEqualTo(42)

            ->when(
                $configuration->a = 153,
                $result = $configuration->a
            )
            ->then
                ->integer($result)
                    ->isEqualTo(153)

            ->when(
                $configuration->new = '💩',
                $result = $configuration->new
            )
            ->then
                ->string($result)
                    ->isEqualTo('💩');
    }

    function case_unset() {

        $this
            ->given(
                $configuration = new CUT(
                    $this->helper->configuration(
                        'configuration.json',
                        ['a' => 42]
                    )
                )
            )
            ->when($result = isset($configuration->a))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when(function() use ($configuration) {
                unset($configuration->a);
            })
            ->and($result = isset($configuration->a))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    function case_save() {

        $this
            ->given(
                $file = $this->helper->configuration(
                    'configuration.json',
                    ['a' => 42]
                ),
                $initialContent = file_get_contents($file),
                $configuration  = new CUT($file)
            )
            ->when($result = $configuration->save())
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string(file_get_contents($file))
                    ->isEqualTo($initialContent)

            ->when(
                $configuration->a = 153,
                $result           = $configuration->save()
            )
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string(file_get_contents($file))
                    ->isNotEqualTo($initialContent)

            ->given($configuration = new CUT($file))
            ->when($result = $configuration->a)
            ->then
                ->integer($result)
                    ->isEqualTo(153);
    }

    function case_do_not_allow_empty() {

        $this
            ->given($file = $this->helper->configuration('configuration.json'))
            ->exception(function() use ($file) {
                new CUT($file);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment')

            ->given($file = $this->helper->configuration('configuration.json'))
            ->exception(function() use ($file) {
                new CUT($file, false);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    function case_allow_empty() {

        $this
            ->given($file = $this->helper->configuration('configuration.json'))
            ->when($result = new CUT($file, true))
            ->then
                ->object($result);
    }

    function case_allow_empty_invalid_json() {

        $this
            ->given(
                $file = $this->helper->configuration('configuration.json'),
                file_put_contents($file, 'x')
            )
            ->exception(function() use ($file) {
                new CUT($file, true);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    function case_empty_file_set() {

        $this
            ->given(
                $file          = $this->helper->configuration('configuration.json'),
                $configuration = new CUT($file, true)
            )
            ->when($configuration->a = 42)
            ->then
                ->integer($configuration->a)
                    ->isEqualTo(42);
    }
}
