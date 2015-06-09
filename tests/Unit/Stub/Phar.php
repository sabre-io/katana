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
namespace Sabre\Katana\Test\Unit\Stub;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Stub\Phar as CUT;

/**
 * Test suite of the PHAR archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 *
 * @tags stub phar
 */
class Phar extends Suite {

    function case_signature() {

        $this
            ->given(
                $filename    = $this->helper->temporaryFile('.phar'),
                $phar        = new CUT($filename),
                $phar['foo'] = 'bar'
            )
            ->when($result = $phar->getSignature())
            ->then
                ->string($result['hash_type'])
                    ->isEqualTo('SHA-1');
    }

    function case_metadata() {

        $this
            ->given($phar = new CUT($this->helper->temporaryFile('.phar')))
            ->when($result = $phar->getMetadata())
            ->then
                ->array($result)
                    ->hasKeys([
                        'author',
                        'license',
                        'copyright',
                        'datetime'
                    ])
                ->string($result['author'])
                    ->isEqualTo('fruux GmbH (https://fruux.com/)')
                ->string($result['license'])
                    ->isEqualTo('Modified BSD License (http://sabre.io/license/)')
                ->string($result['copyright'])
                    ->isEqualTo('Copyright (C) 2015 fruux GmbH (https://fruux.com/)')
                ->string($result['datetime'])
                    ->isEqualTo(date('c', strtotime($result['datetime'])));
    }
}
