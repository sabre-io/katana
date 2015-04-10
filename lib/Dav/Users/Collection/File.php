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

use Sabre\Katana\Exception\Dav as Exception;
use Sabre\DAV;
use JsonSerializable;

/**
 * User file: Represents only one user in the collection.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class File implements DAV\INode, DAV\IFile, JsonSerializable
{
    /**
     * ID of the user.
     *
     * @var int
     */
    public $id       = null;

    /**
     * Username of the user.
     *
     * @var string
     */
    public $username = null;

    /**
     * Constructor.
     * The constructor is called after the attributes are set. Consequently, we
     * check they are valid, i.e. not empty. Should not happen but we prevent
     * a corrupted state by throwing an internal server error exception.
     *
     * @return void
     * @throw  Exception\InternalServerError
     */
    public function __construct()
    {
        if (empty($this->id)) {
            throw new Exception\InternalServerError(
                'User ID cannot be empty. Something unexpected happened.'
            );
        }

        if (empty($this->username)) {
            throw new Exception\InternalServerError(
                'Username cannot be empty. Something unexpected happened.'
            );
        }

        return;
    }

    /**
     * Return the name of the node.
     * This is used to generate the URL.
     *
     * @return string
     */
    public function getName()
    {
        return $this->username;
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
     * Delete the current node.
     *
     * @return void
     */
    public function delete()
    {
        throw new DAV\Exception\Forbidden(
            sprintf('Deleting the user %s is forbidden.', $this->getName())
        );
    }

    /**
     * Return the last modification time, as a Unix timestamp.
     * This information is not stored in the database so we return the current
     * time.
     *
     * @return int
     */
    public function getLastModified()
    {
        return time();
    }

    /**
     * Replace the contents of the file.
     *
     * The data argument is a readable stream resource.
     *
     * After a succesful put operation, you may choose to return an ETag. The
     * ETag must always be surrounded by double-quotes. These quotes must
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
     * @param  resource  $data    Data.
     * @return string|null
     */
    public function put($data)
    {
        throw new DAV\Exception\Forbidden(
            sprintf('Updating the user %s is forbidden.', $this->getName())
        );
    }

    /**
     * Return the data.
     *
     * @return string
     */
    public function get()
    {
        return json_encode($this);
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
        return strlen(json_encode($this));
    }

    /**
     * Serialize in JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'       => $this->id,
            'username' => $this->username
        ];
    }
}
