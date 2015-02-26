<?php

namespace Sabre\Katana\Test\Helper;

use atoum\mock\streams\fs\file;

/**
 * Helper for the configurations.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Configuration
{
    /**
     * Run the helper.
     *
     * @param  string  $filename    Configuration filename.
     * @param  array   $content     Configuration content (as an array, not as
     *                              JSON).
     * @return string
     */
    public function __invoke($filename, Array $content = null)
    {
        $file = (string) file::get($filename);

        if (null !== $content) {
            file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT));
        }

        return $file;
    }
}
