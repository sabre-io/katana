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
namespace Sabre\Katana\Dav\System;

use Sabre\DAV as SabreDav;

/**
 * This trait implements a protected node, only accessible by `principals/admin`.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
trait ProtectedNode {

    /**
     * Define the node name.
     * Must be declared by the classes implementing this trait.
     *
     * @const string
     */
    //const NAME = null;

    /**
     * Get the node's name.
     *
     * @return string
     */
    function getName() {

        return self::NAME;
    }

    /**
     * Get the owner principal
     *
     * This must be a url to a principal, or null if there's no owner.
     *
     * @return string|null
     */
    function getOwner() {

        return 'principals/admin';
    }

    /**
     * Get a group principal
     *
     * This must be a URL to a principal, or null if there's no owner.
     *
     * @return string|null
     */
    function getGroup() {

        return null;
    }

    /**
     * Get a list of ACE's for this node.
     *
     * Each ACE has the following properties:
     *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
     *     currently the only supported privileges
     *   * 'principal', a url to the principal who owns the node
     *   * 'protected' (optional), indicating that this ACE is not allowed to
     *      be updated.
     *
     * @return array
     */
    function getACL() {

        return [
            [
                'principal' => $this->getOwner(),
                'privilege' => '{DAV:}read',
                'protected' => true
            ]
        ];
    }

    /**
     * Updates the ACL.
     *
     * This method will receive a list of new ACE's as an array argument.
     *
     * @param array $acl
     * @return void
     */
    function setACL(array $acl) {

        throw new SabreDav\Exception\Forbidden(
            'Updating ACLs it not allowed on this node.'
        );
    }

    /**
     * Get the list of supported privileges for this node.
     *
     * The returned data structure is a list of nested privileges.
     * See Sabre\DAVACL\Plugin::getDefaultSupportedPrivilegeSet for a simple
     * standard structure.
     *
     * If null is returned from this method, the default privilege set is used,
     * which is fine for most common usecases.
     *
     * @return array|null
     */
    function getSupportedPrivilegeSet() {

        return null;
    }
}
