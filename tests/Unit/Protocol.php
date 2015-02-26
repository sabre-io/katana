<?php

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Protocol as CUT;

/**
 * Test suite of the katana:// protocol.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Protocol extends Suite
{
    public function case_application_public()
    {
        $this
            ->given($path = 'katana://application/public')
            ->when($result = CUT::realPath($path))
            ->then
                ->string($result)
                    ->isEqualTo(
                        realpath(
                            __DIR__ . DS .
                            '..' . DS .
                            '..' . DS .
                            'public'
                        )
                    );
    }

    public function case_application_views()
    {
        $this
            ->given($path = 'katana://application/views')
            ->when($result = CUT::realPath($path))
            ->then
                ->string($result)
                    ->isEqualTo(
                        realpath(
                            __DIR__ . DS .
                            '..' . DS .
                            '..' . DS .
                            'views'
                        )
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
