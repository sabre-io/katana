<?php

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
 * @license http://sabre.io/license/ Modified BSD License
 */
class Protocol extends Core\Protocol\Wrapper
{
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
    public static function realPath($path, $exists = true)
    {
        $path = str_replace(self::SCHEME . '://', 'hoa://', $path);

        return parent::realPath($path, $exists);
    }
}
