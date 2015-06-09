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
namespace Sabre\Katana\Dav\System\Version;

use Sabre\Katana\Server\Updater;
use Sabre\Katana\Dav\System;
use Sabre\DAV as SabreDav;
use Sabre\HTTP\RequestInterface as Request;
use Sabre\HTTP\ResponseInterface as Response;

/**
 * The version plugin is responsible to get current version and check if a new
 * version exists.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Plugin extends SabreDav\ServerPlugin {

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using \Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'version';
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
            'description' => 'Get current version and check if a new one exists.',
            'link'        => 'http://sabre.io/katana/'
        ];
    }

    /**
     * Use this method to tell the server this plugin defines additional
     * HTTP methods.
     *
     * This method is passed a URI. It should only return HTTP methods that are
     * available for the specified URI.
     *
     * @param  string $path
     * @return array
     */
    function getHTTPMethods($path) {

        if (System\Collection::NAME . '/' . Node::NAME !== $path) {
            return [];
        }

        return ['GET'];
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

        $server->on('method:GET', [$this, 'httpGet']);
    }

    /**
     * @return bool
     */
    function httpGet(Request $request, Response $response) {

        if (System\Collection::NAME . '/' . Node::NAME !== $request->getPath()) {
            return;
        }

        $payload = [
            'current_version' => SABRE_KATANA_VERSION
        ];

        $extra = [];

        if (true === $request->hasHeader('Referer')) {
            $extra['referer'] = $request->getHeader('Referer');
        }

        $updatesDotJson = Updater::getUpdateUrl(
            Updater::DEFAULT_UPDATE_SERVER,
            $extra
        );
        $versions = @file_get_contents($updatesDotJson);

        if (!empty($versions)) {
            $versions        = json_decode($versions, true);
            $versionsToFetch = Updater::filterVersions(
                $versions,
                SABRE_KATANA_VERSION,
                Updater::FORMAT_PHAR
            );
            $payload['next_versions'] = array_keys($versionsToFetch);
        }

        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($payload));

        return false;
    }
}
