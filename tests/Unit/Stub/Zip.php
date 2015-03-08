<?php

namespace Sabre\Katana\Test\Unit\Stub;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Stub\Zip as CUT;
use Phar as PHPPhar;

/**
 * Test suite of the ZIP archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 *
 * @tags stub zip
 */
class Zip extends Suite
{
    public function case_format()
    {
        $this
            ->when($zip = new CUT($this->helper->temporaryFile('.zip')))
            ->then
                ->boolean($zip->isFileFormat(PHPPhar::ZIP))
                    ->isTrue();
    }
}
