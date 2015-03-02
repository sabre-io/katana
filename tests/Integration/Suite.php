<?php

namespace Sabre\Katana\Test\Integration;

use Sabre\Katana\Test\Unit\Suite as UnitSuite;

/**
 * Integration test suite parent class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Suite extends UnitSuite
{
    const defaultNamespace = '#\\\Test\\\Integration\\\#';
}
