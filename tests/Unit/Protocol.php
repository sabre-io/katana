<?php

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Protocol as CUT;

/**
 * Test suite of the katana:// protocol.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 *
 * @tags protocol
 */
class Protocol extends Suite
{
    public function case_application_public()
    {
        $this
            ->given($path = 'katana://public/')
            ->when($result = CUT::realPath($path))
            ->then
                ->string($result)
                    ->isEqualTo(
                        realpath(
                            __DIR__ . DS .
                            '..' . DS .
                            '..' . DS .
                            'public'
                        ) . DS
                    );
    }

    public function case_application_views()
    {
        $this
            ->given($path = 'katana://views/')
            ->when($result = CUT::realPath($path))
            ->then
                ->string($result)
                    ->isEqualTo(
                        realpath(
                            __DIR__ . DS .
                            '..' . DS .
                            '..' . DS .
                            'views'
                        ) . DS
                    );
    }

    public function case_data_root()
    {
        $this
            ->given($path = 'katana://data/')
            ->when($result = CUT::realPath($path))
            ->then
                ->string($result)
                    ->isEqualTo(
                        realpath(
                            __DIR__ . DS .
                            '..' . DS .
                            '..' . DS .
                            'data'
                        ) . DS
                    );
    }
}
