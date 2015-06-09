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

use Sabre\DAVACL as SabreDavAcl;
use Sabre\DAV as SabreDav;
use Sabre\Uri as SabreUri;

/**
 * This class represents a directory inside the home collection.
 * We automatically re-create and set specific ACL to the `public/` directory.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Directory extends SabreDavAcl\FS\Collection {

    /**
     * Relative path, aka storage path of the home directory.
     *
     * @var string
     */
    protected static $relativePath = null;

    /**
     * Returns a specific child node, referenced by its name
     *
     * This method must throw Sabre\DAV\Exception\NotFound if the node does not
     * exist.
     *
     * @param string $name
     * @throws DAV\Exception\NotFound
     * @return DAV\INode
     */
    function getChild($name) {

        $path = $this->path . DS . $name;

        if (!file_exists($path)) {
            throw new SabreDav\Exception\NotFound('File could not be located');
        }

        if ('.' === $name || '..' === $name) {
            throw new SabreDav\Exception\Forbidden('Permission denied to . and ..');
        }

        if (is_dir($path)) {
            return new self($path, $this->acl, $this->owner);
        } else {
            $acl = $this->acl;

            if (true === $this->isPublic()) {
                $acl = [
                    [
                        'privilege' => '{DAV:}read',
                        'principal' => '{DAV:}all',
                        'protected' => true
                    ],
                    [
                        'privilege' => '{DAV:}write',
                        'principal' => $this->owner,
                        'protected' => true
                    ]
                ];
            }

            return new SabreDavAcl\FS\File($path, $acl, $this->owner);
        }
    }

    /**
     * Set relative path.
     *
     * @param  string  $relativePath    Relative path.
     * @return void
     */
    function setRelativePath($relativePath) {

        self::$relativePath = $relativePath;
    }

    /**
     * Get relative path.
     *
     * @return string
     */
    static function getRelativePath() {

        return self::$relativePath;
    }

    /**
     * Check if the current path is inside the `public/` directory of the
     * current principal.
     *
     * @return bool
     */
    function isPublic() {

        list(, $principalBaseName) = SabreUri\split($this->owner);

        $publicPath =
            $this->getRelativePath() . DS .
            $principalBaseName . DS .
            'public';

        return
            $publicPath
            ===
            substr($this->path, 0, min(strlen($publicPath), strlen($this->path)));
    }
}
