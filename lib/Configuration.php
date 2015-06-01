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

use StdClass;

/**
 * Open, read and write JSON configurations. No lock on the filesystem.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Configuration {

    /**
     * Name of the configuration file.
     *
     * @var string
     */
    protected $filename      = null;

    /**
     * Configurations as a StdClass.
     *
     * @var StdClass
     */
    protected $configuration = null;

    /**
     * Read the configurations.
     *
     * @param  string  $filename      Filename to a valid JSON file.
     * @param  bool    $allowEmpty    Whether we allow the file to be empty.
     * @return void
     * @throw  Exception\Environment
     */
    function __construct($filename, $allowEmpty = false) {

        if (false === file_exists($filename)) {
            throw new Exception\Environment(
                'The %s configuration file is not found.',
                0,
                $filename
            );
        }

        $this->filename = $filename;
        $content        = file_get_contents($filename);

        if (empty($content) && true === $allowEmpty) {

            $this->configuration = new StdClass();
            return;
        }

        $decodedJson = @json_decode($content);

        if (!($decodedJson instanceof StdClass)) {

            if (is_array($decodedJson) && empty($decodedJson)) {
                $decodedJson = new StdClass();
            } else {
                throw new Exception\Environment(
                    'The %s configuration contains invalid JSON data.',
                    1,
                    $filename
                );
            }

        }

        $this->configuration = $decodedJson;
    }

    /**
     * Check if an entry exists.
     *
     * @param  string  $name    Entry name.
     * @return bool
     */
    function __isset($name) {

        return isset($this->configuration->$name);
    }

    /**
     * Read an entry.
     *
     * @param  string  $name    Entry name.
     * @return mixed
     */
    function __get($name) {

        return $this->configuration->$name;
    }

    /**
     * Set an entry value. If it does not exist, it will create it.
     *
     * @param  string  $name     Entry name.
     * @param  mixed   $value    Entry value.
     * @return void
     */
    function __set($name, $value) {

        $this->configuration->$name = $value;
    }

    /**
     * Unset an entry.
     *
     * @param  string  $name     Entry name.
     * @return void
     */
    function __unset($name) {

        unset($this->configuration->$name);
    }

    /**
     * Save the configurations into the original filename.
     *
     * @return bool
     */
    function save() {

        return false !== file_put_contents(
            $this->getFilename(),
            json_encode($this->configuration, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Get the name of the read configuration file.
     *
     * @return string
     */
    function getFilename() {

        return $this->filename;
    }
}
