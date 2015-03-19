<?php

namespace Mock;

use Sabre\Katana\Server\Server as OriginalServer;
use Sabre\Katana\Configuration;
use Sabre\Katana\Server\Installer;
use Sabre\Katana\Test\Helper;

class Server extends OriginalServer
{
    const ADMINISTRATOR_LOGIN    = 'gordon';
    const ADMINISTRATOR_EMAIL    = 'katana@domain.tld';
    const ADMINISTRATOR_PASSWORD = '🔒';
    const ADMINISTRATOR_REALM    = '627dc738650b9be482b3aa3ad56c306f0e73107e';

    public $request  = null;
    public $response = null;

    protected function initializeServer()
    {
        parent::initializeServer();

        $server         = $this->getServer();
        $server->sapi   = new Sapi();
        $this->request  = &$server->httpRequest;
        $this->response = &$server->httpResponse;

        return;
    }

    protected function initializeConfiguration()
    {
        $configuration        = new Helper\Configuration();
        $sqlite               = new Helper\SQLite();
        $this->_configuration = new Configuration(
            $configuration(
                'configuration.json',
                [
                    'base_url'         => '/',
                    'authentification' => [
                        'realm' => static::ADMINISTRATOR_REALM
                    ],
                    'database' => [
                        'dsn'      => $sqlite(true),
                        'username' => '',
                        'password' => ''
                    ]
                ]
            )
        );

        return;
    }

    protected function initializeDatabase()
    {
        $database = Installer::createDatabase($this->getConfiguration());
        Installer::createAdministratorProfile(
            $this->getConfiguration(),
            $database,
            static::ADMINISTRATOR_LOGIN,
            static::ADMINISTRATOR_EMAIL,
            static::ADMINISTRATOR_PASSWORD
        );
        unset($database);

        return parent::initializeDatabase();
    }
}
