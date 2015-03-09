<?php

namespace Sabre\Katana\Server;

use Sabre\Katana\Exception;

/**
 * A set of utilities for the updater.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Updater
{
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
     * @return string
     */
    public static function getUpdateUrl($updateServer = null)
    {
        if (null === $updateServer) {
            $updateServer = static::DEFAULT_UPDATE_SERVER;
        }

        return sprintf(
            '%supdates.json?version=%s',
            $updateServer,
            SABRE_KATANA_VERSION
        );
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
     * @param  array  $versions    List of versions.
     * @param  string  $currentVersion    Current version, i.e. lowest version
     *                                    to keep.
     * @param  int     $format            Please, see `FORMAT_*` constant.
     * @return array
     * @throw  Exception\Update
     */
    public static function filterVersions(array $versions, $currentVersion, $format)
    {
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
