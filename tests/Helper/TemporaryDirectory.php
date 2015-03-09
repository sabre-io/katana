<?php

namespace Sabre\Katana\Test\Helper;

/**
 * Helper to get a temporary directory.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class TemporaryDirectory
{
    /**
     * Run the helper.
     *
     * @param  string   $directoryName    Directory name.
     * @param  boolean  $create           Create if it does not exist.
     * @return string
     */
    public function __invoke($directoryName, $create = false)
    {
        $path = sys_get_temp_dir() . DS . $directoryName;

        if (true === $create && false === file_exists($path)) {
            mkdir($path);
        }

        return $path;
    }
}
