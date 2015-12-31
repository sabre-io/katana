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

namespace Sabre\Katana\DavAcl\File;

use Sabre\DAV as SabreDav;
use Sabre\Uri as SabreUri;
use Hoa\File as HoaFile;
use Hoa\Mime;

/**
 * The file plugin is responsible to keep the home directory up-to-date.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Plugin extends SabreDav\ServerPlugin {

    /**
     * DAV server.
     *
     * @var DAV\Server;
     */
    protected $server      = null;

    /**
     * Storage path.
     *
     * @var string
     */
    protected $storagePath = null;

    /**
     * Set the storage path.
     *
     * @param  string  $storagePath    Storage path.
     * @return void
     */
    function __construct($storagePath) {

        $this->storagePath = $storagePath;
    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using \Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'file';
    }

    /**
     * Returns a bunch of meta-data about the plugin.
     *
     * Providing this information is optional, and is mainly displayed by the
     * Browser plugin.
     *
     * The description key in the returned array may contain html and will not
     * be sanitized.
     *
     * @return array
     */
    function getPluginInfo() {

        return [
            'name'        => $this->getPluginName(),
            'description' => 'Files support.',
            'link'        => 'http://sabre.io/katana/'
        ];
    }

    /**
     * This initializes the plugin.
     *
     * This function is called by Sabre\DAV\Server, after
     * addPlugin is called.
     *
     * This method should set up the required event subscriptions.
     *
     * @param  SabreDav\Server $server    Server.
     * @return void
     */
    function initialize(SabreDav\Server $server) {

        $this->server = $server;

        $this->server->on('propFind',    [$this, 'propFind']);
        $this->server->on('afterUnbind', [$this, 'afterUnbind']);
    }

    /**
     * Triggered by a `PROPFIND` or `GET`. The goal is to set an appropriated
     * Content-Type.
     *
     * @param  SabreDav\PropFind  $propFind    PropFind object.
     * @param  SabreDav\INode     $node        Node.
     */
    function propFind(SabreDav\PropFind $propFind, SabreDav\INode $node) {

        $propFind->handle(
            '{DAV:}getcontenttype',
            function() use ($propFind) {
                Mime::compute(__DIR__ . '/../../../resource/mime.types');

                $extension = pathinfo($propFind->getPath(), PATHINFO_EXTENSION);

                return Mime::getMimeFromExtension($extension);
            }
        );
    }

    /**
     * Triggered by a `DELETE`, `COPY` or `MOVE`. The goal is to remove the
     * home directory of the principal.
     *
     * @param  string  $path    Path.
     * @return bool
     */
    function afterUnbind($path) {

        list($collection, $principalName) = SabreUri\split($path);

        if ('principals' !== $collection) {
            return false;
        }

        $out  = true;
        $path = $this->storagePath . DS . $principalName;

        if (is_dir($path)) {
            $directory = new HoaFile\Directory($this->storagePath . DS . $principalName);
            $out       = $directory->delete();

            $directory->close();
        }

        return $out;
    }
}
