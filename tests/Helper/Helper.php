<?php

namespace Sabre\Katana\Test\Helper;

use RuntimeException;

/**
 * Helper manager.
 * A helper is a callable.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Helper
{
    /**
     * All helpers.
     *
     * @var array
     */
    protected $_helpers = [];

    /**
     * Register a new helper.
     *
     * @param  string    $name      Helper's name.
     * @param  callable  $helper    Helper.
     * @return void
     */
    public function registerHelper($name, callable $helper)
    {
        $this->_helpers[$name] = $helper;

        return;
    }

    /**
     * Execute a helper.
     *
     * @param  string  $name         Helper's name.
     * @param  array   $arguments    Helper's arguments.
     * @return mixed
     */
    public function __call($name, Array $arguments)
    {
        if (!isset($this->_helpers[$name])) {
            throw new RuntimeException(sprintf('Helper %s does not exist.', $name));
        }

        return call_user_func_array(
            $this->_helpers[$name],
            $arguments
        );
    }
}
