<?php

namespace Sabre\Katana\Server;

use Sabre\CalDAV;
use Sabre\CardDAV;
use Sabre\DAV;

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
        $this->initialize();

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
