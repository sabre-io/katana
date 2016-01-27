<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2016 fruux GmbH (https://fruux.com/)
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
namespace Sabre\Katana\Server;

use Sabre\Katana\Exception;
use Sabre\Katana\Version;

/**
 * A set of utilities for the updater.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Updater {

    /**
     * Default update server URL.
     *
     * @const string
     */
    const DEFAULT_UPDATE_SERVER = 'http://sabre.io/katana/update-server/';

    /**
     * Format: PHAR.
     *
     * @const int
     */
    const FORMAT_PHAR           = 1;

    /**
     * Format: ZIP.
     *
     * @const int
     */
    const FORMAT_ZIP            = 2;

    /**
     * Get the URL of the `updates.json` file, containing the list of updates.
     *
     * @param  string  $updateServer    Update server URL.
     * @param  array   $queries         Extra queries to add.
     * @return string
     */
    static function getUpdateUrl($updateServer = null, array $queries = []) {

        if (null === $updateServer) {
            $updateServer = static::DEFAULT_UPDATE_SERVER;
        }

        $out                 = $updateServer . 'updates.json';
        $queries['version']  = Version::VERSION;
        $out                .= '?' . http_build_query($queries, '', '&', PHP_QUERY_RFC3986);

        return $out;
    }

    /**
     * Filter versions.o
     * Expected data for `$versions`:
     *
     *     [
     *         '1.0.1' => [
     *             'phar' => 'https://…',
     *             'zip'  => 'https://…'
     *         ],
     *         '1.0.0' => [
     *             'phar' => 'https://…',
     *             'zip'  => 'https://…'
     *         ],
     *         …
     *     ]
     *
     * Returns versions associated to an URL according to the format:
     *
     *     [
     *         '1.0.1' => 'https://…',
     *         …
     *     ]
     *
     * @param  array   $versions          List of versions.
     * @param  string  $currentVersion    Current version, i.e. lowest version
     *                                    to keep.
     * @param  int     $format            Please, see `FORMAT_*` constant.
     * @return array
     * @throw  Exception\Update
     */
    static function filterVersions(array $versions, $currentVersion, $format) {

        $out = [];

        foreach ($versions as $version => $urls) {

            if (!is_array($urls) ||
                !isset($urls['phar']) ||
                !isset($urls['zip'])) {
                throw new Exception\Update(
                    'Cannot filter versions, the list seems corrupted.'
                );
            }

            if (-1 === version_compare($currentVersion, $version)) {

                if (0 !== (static::FORMAT_PHAR & $format)) {
                    $out[$version] = $urls['phar'];
                } else {
                    $out[$version] = $urls['zip'];
                }

            }

        }

        return $out;
    }
}
