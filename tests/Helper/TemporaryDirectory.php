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
namespace Sabre\Katana\Test\Helper;

/**
 * Helper to get a temporary directory.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class TemporaryDirectory {

    /**
     * Run the helper.
     *
     * @param  string  $directoryName    Directory name.
     * @param  bool    $create           Create if it does not exist.
     * @return string
     */
    function __invoke($directoryName, $create = false) {

        $path = sys_get_temp_dir() . DS . $directoryName;

        if (true === $create && false === file_exists($path)) {
            mkdir($path);
        }

        return $path;
    }
}
