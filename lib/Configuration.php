<?php

namespace Sabre\Katana;

use Sabre\Katana\Exception;
use StdClass;

/**
 * Open, read and write JSON configurations. No lock on the filesystem.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Configuration
{
    /**
     * Name of the configuration file.
     *
     * @var string
     */
    protected $_filename      = null;

    /**
     * Configurations as a StdClass.
     *
     * @var StdClass
     */
    protected $_configuration = null;

    /**
     * Read the configurations.
     *
     * @param  string  $filename    Filename to a valid JSON file.
     * @return void
     * @throw  Exception\Environment
     */
    public function __construct($filename)
    {
        if (false === file_exists($filename)) {
            throw new Exception\Environment(
                sprintf('The %s configuration file is not found.', $filename)
            );
        }

        $this->_filename = $filename;
        $decodedJson     = @json_decode(file_get_contents($filename));

        if (!($decodedJson instanceof StdClass)) {

            if (is_array($decodedJson) && empty($decodedJson)) {
                $decodedJson = new StdClass();
            } else {
                throw new Exception\Environment(
                    sprintf(
                        'The %s configuration contains invalid JSON data.',
                        $filename
                    )
                );
            }

        }

        $this->_configuration = $decodedJson;

        return;
    }

    /**
     * Check if an entry exists.
     *
     * @param  string  $name    Entry name.
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_configuration->$name);
    }

    /**
     * Read an entry.
     *
     * @param  string  $name    Entry name.
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_configuration->$name;
    }

    /**
     * Set an entry value. If it does not exist, it will create it.
     *
     * @param  string  $name     Entry name.
     * @param  mixed   $value    Entry value.
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_configuration->$name = $value;

        return;
    }

    /**
     * Unset an entry.
     *
     * @param  string  $name     Entry name.
     * @return void
     */
    public function __unset($name)
    {
        unset($this->_configuration->$name);

        return;
    }

    /**
     * Save the configurations into the original filename.
     *
     * @return boolean
     */
    public function save()
    {
        return false !== file_put_contents(
            $this->getFilename(),
            json_encode($this->_configuration)
        );
    }

    /**
     * Get the name of the read configuration file.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }
}