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

namespace Sabre\Katana\DavAcl\File;

use Sabre\DAVACL as SabreDavAcl;
use Sabre\Uri as SabreUri;
use Hoa\File as HoaFile;

/**
 * This class represents a collection of home directories. A home directory is a
 * principal specific WebDAV folder. Only the owner can read and write inside
 * this folder, except for the `public/` directory where everyone can read.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Home extends SabreDavAcl\FS\HomeCollection {

    /**
     * Returns a principals' collection of files.
     *
     * The passed array contains principal information, and is guaranteed to
     * at least contain a uri item. Other properties may or may not be
     * supplied by the authentication backend.
     *
     * @param array $principalInfo
     * @return void
     */
    function getChildForPrincipal(array $principalInfo) {

        $owner = $principalInfo['uri'];
        $acl   = [
            [
                'privilege' => '{DAV:}read',
                'principal' => $owner,
                'protected' => true,
            ],
            [
                'privilege' => '{DAV:}write',
                'principal' => $owner,
                'protected' => true,
            ],
        ];

        list(, $principalBaseName) = SabreUri\split($owner);

        $path = $this->storagePath . DS . $principalBaseName;

        if (!is_dir($path)) {
            HoaFile\Directory::create(
                $path,
                HoaFile\Directory::MODE_CREATE_RECURSIVE
            );
        }

        $public = $path . DS . 'public';

        if (!is_dir($public)) {
            HoaFile\Directory::create(
                $public,
                HoaFile\Directory::MODE_CREATE_RECURSIVE
            );
        }

        $out = new Directory($path, $acl, $owner);
        $out->setRelativePath($this->storagePath);

        return $out;
    }
}
