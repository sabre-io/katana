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

namespace Sabre\Katana\Stub;

use PharData;

/**
 * Create a ZIP archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Zip extends PharData {

    /**
     * Open a ZIP archive. If it does not exist, attempt to create it.
     *
     * @param  string  $filename    Filename (see original documentation).
     * @param  int     $flags       Flags (see original documentation).
     * @param  string  $alias       Alias (see original documentation).
     * @return void
     */
    function __construct($filename, $flags = null, $alias = null) {

        return parent::__construct($filename, $flags, $alias, Phar::ZIP);
    }
}
