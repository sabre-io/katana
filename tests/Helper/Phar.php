<?php

namespace Sabre\Katana\Test\Helper;

use atoum\mock\streams\fs\file;

/**
 * Helper to get the filename of a fresh SQLite database.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Phar
{
    /**
     * Run the helper.
     *
     * @param  string  $filename    Filename.
     * @return string
     */
    public function __invoke($filename)
    {
        return sys_get_temp_dir() . DS . $filename;
    }
}
