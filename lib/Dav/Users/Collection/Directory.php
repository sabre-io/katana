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

use Sabre\Katana\Database;
use Sabre\DAV;

/**
 * Users directory: Collection of all users.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Directory implements DAV\INode, DAV\ICollection
{
    /**
     * Directory name, e.g. “users”.
     *
     * @var string
     */
    protected $_name     = null;

    /**
     * Database.
     *
     * @var Database
     */
    protected $_database = null;

    /**
     * Constructor.
     *
     * @param  string    $name        Directory name.
     * @param  Database  $database    Database connection.
     * @return void
     */
    public function __construct($name, Database $database)
    {
        $this->_name     = $name;
        $this->_database = $database;

        return;
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
        return $this->_name;
    }

    /**
     * Renames the node.
     * This is not allowed for this collection. So we basically always throw an
     * exception.
     *
     * @param  string  $name  The new name
     * @throw  DAV\Exception\Forbidden
     */
    public function setName($name)
    {
        throw new DAV\Exception\Forbidden(
            sprintf(
                'Renaming the collection %s to %s is forbidden.',
                $this->getName(),
                $name
            )
        );
    }

    /**
     * Delete the current node.
     * This is not allowed for this collection. So we basically always throw an
     * exception.
     *
     * @throw  DAV\Exception\Forbidden
     */
    public function delete()
    {
        throw new DAV\Exception\Forbidden(
            sprintf('Deleting the collection %s is forbidden.', $this->getName())
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
     * Create a new file in the directory.
     *
     * Data will either be supplied as a stream resource, or in certain cases
     * as a string. Keep in mind that you may have to support either.
     *
     * After successful creation of the file, you may choose to return the ETag
     * of the new file here.
     *
     * The returned ETag must be surrounded by double-quotes (the quotes should
     * be part of the actual string).
     *
     * If you cannot accurately determine the ETag, you should not return it.
     * If you don't store the file exactly as-is (you're transforming it
     * somehow) you should also not return an ETag.
     *
     * This means that if a subsequent GET to this new file does not exactly
     * return the same contents of what was submitted here, you are strongly
     * recommended to omit the ETag.
     *
     * @param  string           $name    Name of the user.
     * @param  resource|string  $data    Initial payload.
     * @return null|string
     */
    public function createFile($name, $data = null)
    {
        file_put_contents('/tmp/a', __METHOD__ . ' ' . $name . "\n", FILE_APPEND);

        return;
    }

    /**
     * Creates a new sub-directory.
     *
     * @param  string $name
     * @throw  DAV\Exception\Forbidden
     */
    public function createDirectory($name)
    {
        throw new DAV\Exception\Forbidden(
            sprintf(
                'Creating sub-directory %s in the collection %s is forbidden.',
                $name,
                $this->getName()
            )
        );
    }

    /**
     * Returns a specific child node, referenced by its name
     *
     * This method must throw Sabre\DAV\Exception\NotFound if the node does not
     * exist.
     *
     * @param  string  $name    Name of the user.
     * @return DAV\INode
     * @throw  DAV\Exception\NotFound
     */
    public function getChild($name)
    {
        $database  = $this->getDatabase();
        $statement = $database->prepare(
            'SELECT id, username FROM users WHERE username = :username'
        );
        $statement->execute(['username' => $name]);

        $file = $statement->fetchObject(__NAMESPACE__ . '\File');

        if (false === $file) {
            throw new DAV\Exception\NotFound(
                sprintf('User %s is not found.', $name)
            );
        }

        return $file;
    }

    /**
     * Returns an array with all the child nodes
     *
     * @return DAV\INode[]
     */
    public function getChildren()
    {
        $database  = $this->getDatabase();
        $statement = $database->prepare('SELECT id, username FROM users');
        $statement->execute();

        return $statement->fetchAll(
            $database::FETCH_CLASS,
            __NAMESPACE__ . '\File'
        );
    }

    /**
     * Check if a child-node with the specified name exists.
     *
     * @param  string  $name    Name of the user.
     * @return bool
     */
    public function childExists($name)
    {
        $database  = $this->getDatabase();
        $statement = $database->prepare(
            'SELECT EXISTS (SELECT id FROM users WHERE username = :username)'
        );
        $statement->execute(['username' => $name]);

        return boolval($statement->fetchColumn(0));
    }

    /**
     * Get database.
     *
     * @return Database
     */
    protected function getDatabase()
    {
        return $this->_database;
    }
}
