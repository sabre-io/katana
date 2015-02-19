<?php

namespace Sabre\Katana\Server;

use Sabre\CalDAV;
use Sabre\CardDAV;
use Sabre\DAV;

/**
 * Server main class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Server
{
    /**
     * sabre/dav server.
     *
     * @var Sabre\
     */
    protected $_server = null;

    /**
     * Construct and initialize the server.
     */
    public function __construct()
    {
        $this->_server = new DAV\Server();

        return;
    }

    /**
     * Run the server, i.e. consume the current request.
     */
    public function run()
    {
        $this->_server->exec();

        return;
    }
}
