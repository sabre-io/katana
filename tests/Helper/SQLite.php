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
class SQLite
{
    /**
     * Run the helper.
     *
     * @return string
     */
    public function __invoke()
    {
        return 'sqlite:' . stream_get_meta_data(tmpfile())['uri'];
    }
}
