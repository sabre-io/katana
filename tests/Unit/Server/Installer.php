<?php

namespace Sabre\Katana\Test\Unit\Server;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Server\Installer as LUT;

/**
 * Test suite of the installer.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Installer extends Suite
{
    public function case_is_installed()
    {
        $this
            ->given($this->function->file_exists = true)
            ->when($result = LUT::isInstalled())
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_is_not_installed()
    {
        $this
            ->given($this->function->file_exists = false)
            ->when($result = LUT::isInstalled())
            ->then
                ->boolean($result)
                    ->isFalse();
    }
}
