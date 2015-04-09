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

namespace Sabre\Katana\Dav\Users\Collection;

use Sabre\DAV;

/**
 * User file: Represents only one user in the collection.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class File implements DAV\INode, DAV\IFile
{
    protected $_name = null;

    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Deleted the current node
     *
     * @return void
     */
    public function delete()
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
    }

    /**
     * Returns the name of the node.
     *
     * This is used to generate the url.
     *
     * @return string
     */
    public function getName()
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
        return $this->_name;
    }

    /**
     * Renames the node
     *
     * @param string $name The new name
     * @return void
     */
    public function setName($name)
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
    }

    /**
     * Returns the last modification time, as a unix timestamp
     *
     * @return int
     */
    public function getLastModified()
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
        return 0;
    }

    /**
     * Replaces the contents of the file.
     *
     * The data argument is a readable stream resource.
     *
     * After a succesful put operation, you may choose to return an ETag. The
     * etag must always be surrounded by double-quotes. These quotes must
     * appear in the actual string you're returning.
     *
     * Clients may use the ETag from a PUT request to later on make sure that
     * when they update the file, the contents haven't changed in the mean
     * time.
     *
     * If you don't plan to store the file byte-by-byte, and you return a
     * different object on a subsequent GET you are strongly recommended to not
     * return an ETag, and just return null.
     *
     * @param resource $data
     * @return string|null
     */
    public function put($data)
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
    }

    /**
     * Returns the data
     *
     * This method may either return a string or a readable stream resource
     *
     * @return mixed
     */
    public function get()
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
        return 'foobar';
    }

    /**
     * Returns the mime-type for a file
     *
     * If null is returned, we'll assume application/octet-stream
     *
     * @return string|null
     */
    public function getContentType()
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
        return 'application/json';
    }

    /**
     * Returns the ETag for a file
     *
     * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
     *
     * Return null if the ETag can not effectively be determined.
     *
     * The ETag must be surrounded by double-quotes, so something like this
     * would make a valid ETag:
     *
     *   return '"someetag"';
     *
     * @return string|null
     */
    public function getETag()
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
    }

    /**
     * Returns the size of the node, in bytes
     *
     * @return int
     */
    public function getSize()
    {
        file_put_contents('/tmp/a', __METHOD__ . "\n", FILE_APPEND);
        return 6;
    }
}
