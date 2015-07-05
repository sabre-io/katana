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

namespace Sabre\Katana\Dav\System;

use Sabre\DAV as SabreDav;
use Sabre\DAVACL as SabreDavAcl;

/**
 * The system collection. A set of tools for sabre/katana.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Collection extends SabreDav\SimpleCollection implements SabreDavAcl\IACL {

    use ProtectedNode;

    /**
     * Define the node name.
     *
     * @const string
     */
    const NAME = 'system';

    /**
     * Constructor.
     *
     * @params  array  $children    Children.
     */
    function __construct(array $children) {

        parent::__construct(self::NAME, $children);
    }
}
