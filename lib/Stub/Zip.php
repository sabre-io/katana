<?php

namespace Sabre\Katana\Stub;

use PharData;

/**
 * Create a ZIP archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Zip extends PharData
{
    /**
     * Open a ZIP archive. If it does not exist, attempt to create it.
     *
     * @param  string  $filename    Filename (see original documentation).
     * @param  int     $flags       Flags (see original documentation).
     * @param  string  $alias       Alias (see original documentation).
     * @return void
     */
    public function __construct($filename, $flags = null, $alias = null)
    {
        return parent::__construct($filename, $flags, $alias, Phar::ZIP);
    }
}
