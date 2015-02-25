<?php

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Configuration as LUT;

/**
 * Test suite of the configuration component.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Configuration extends Suite
{
    public function case_file_not_found()
    {
        $this
            ->exception(function () {
                new LUT('/foo/42');
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    public function case_invalid_json()
    {
        $this
            ->given(
                $file = $this->helper->configuration('configuration.json'),
                file_put_contents($file, 'x')
            )
            ->exception(function () use($file) {
                new LUT($file);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    public function case_get_filename()
    {
        $this
            ->given(
                $file = $this->helper->configuration(
                    'configuration.json',
                    []
                ),
                $configuration = new LUT($file)
            )
            ->when($result = $configuration->getFilename())
            ->then
                ->string($result)
                    ->isEqualTo($file);
    }

    public function case_isset()
    {
        $this
            ->given(
                $configuration = new LUT(
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

    public function case_get()
    {
        $this
            ->given(
                $configuration = new LUT(
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

    public function case_set()
    {
        $this
            ->given(
                $configuration = new LUT(
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

    public function case_unset()
    {
        $this
            ->given(
                $configuration = new LUT(
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

            ->when(function () use($configuration) {
                unset($configuration->a);
            })
            ->and($result = isset($configuration->a))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_save()
    {
        $this
            ->given(
                $file = $this->helper->configuration(
                    'configuration.json',
                    ['a' => 42]
                ),
                $initialContent = file_get_contents($file),
                $configuration  = new LUT($file)
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

            ->given($configuration = new LUT($file))
            ->when($result = $configuration->a)
            ->then
                ->integer($result)
                    ->isEqualTo(153);
    }

    public function case_do_not_allow_empty()
    {
        $this
            ->given($file = $this->helper->configuration('configuration.json'))
            ->exception(function () use($file) {
                new LUT($file);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment')

            ->given($file = $this->helper->configuration('configuration.json'))
            ->exception(function () use($file) {
                new LUT($file, false);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    public function case_allow_empty()
    {
        $this
            ->given($file = $this->helper->configuration('configuration.json'))
            ->when($result = new LUT($file, true))
            ->then
                ->object($result);
    }

    public function case_allow_empty_invalid_json()
    {
        $this
            ->given(
                $file = $this->helper->configuration('configuration.json'),
                file_put_contents($file, 'x')
            )
            ->exception(function () use($file) {
                new LUT($file, true);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Environment');
    }

    public function case_empty_file_set()
    {
        $this
            ->given(
                $file          = $this->helper->configuration('configuration.json'),
                $configuration = new LUT($file, true)
            )
            ->when($configuration->a = 42)
            ->then
                ->integer($configuration->a)
                    ->isEqualTo(42);
    }
}
