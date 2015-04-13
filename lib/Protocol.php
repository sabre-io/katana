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

namespace Sabre\Katana;

use Hoa\Core;

/**
 * Protocol wrapper.
 *
 * The `katana://` protocol acts like a virtual filesystem. We can see it as a
 * set of symbolic links. Use it to access to resources.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Protocol extends Core\Protocol\Wrapper {
    /**
     * The protocol scheme.
     *
     * @const string
     */
    const SCHEME = 'katana';

    /**
     * Get the real path of the given URL.
     * Could return false if the path cannot be reached.
     *
     * @access  public
     * @param   string  $path      Path (or URL).
     * @param   bool    $exists    If true, try to find the first that exists,
     * @return  mixed
     */
    static function realPath($path, $exists = true)
    {
        $path = str_replace(self::SCHEME . '://', 'hoa://', $path);

        return parent::realPath($path, $exists);
    }
}
