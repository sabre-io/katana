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

use RuntimeException;

/**
 * Helper manager.
 * A helper is a callable.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Helper {

    /**
     * All helpers.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Register a new helper.
     *
     * @param  string    $name      Helper's name.
     * @param  callable  $helper    Helper.
     * @return void
     */
    function registerHelper($name, callable $helper) {

        $this->helpers[$name] = $helper;
    }

    /**
     * Execute a helper.
     *
     * @param  string  $name         Helper's name.
     * @param  array   $arguments    Helper's arguments.
     * @return mixed
     */
    function __call($name, array $arguments) {

        if (!isset($this->helpers[$name])) {
            throw new RuntimeException(sprintf('Helper %s does not exist.', $name));
        }

        return call_user_func_array(
            $this->helpers[$name],
            $arguments
        );
    }
}
