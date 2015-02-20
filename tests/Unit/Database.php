<?php

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Database as LUT;

/**
 * Test suite of the database component.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Database extends Suite
{
    public function case_extends_pdo()
    {
        $this
            ->given($sqlite = $this->helper->sqlite())
            ->when($result = new LUT($sqlite))
            ->then
                ->object($result)
                    ->isInstanceOf('PDO');
    }
}
