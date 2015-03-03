<?php

namespace Sabre\Katana\Test\Integration\Server;

use Sabre\Katana\Test\Integration\Suite;
use Sabre\Katana\Server\Installer as CUT;

/**
 * Test suite of the installer.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 *
 * @tags installation configuration database sqlite mysql authentification administration
 */
class Installer extends Suite
{
    public function case_full_installation_with_sqlite()
    {
        $this
            ->given(
                $payload = (object) [
                    'baseurl'  => '/katana/',
                    'login'    => 'admin',
                    'email'    => 'gordon@freeman.hl',
                    'password' => '🔒 💩',
                    'database' => (object) [
                        'driver'   => 'sqlite',
                        'host'     => '',
                        'port'     => '',
                        'name'     => '',
                        'username' => '',
                        'password' => ''
                    ]
                ]
            )
            ->case_full_installation($payload);
    }

    public function case_full_installation_with_mysql()
    {
        $this
            ->given(
                $payload = (object) [
                    'baseurl'  => '/katana/',
                    'login'    => 'admin',
                    'email'    => 'gordon@freeman.hl',
                    'password' => '🔒 💩',
                    'database' => (object) [
                        'driver'   => 'mysql',
                        'host'     => HELPER_MYSQL_HOST,
                        'port'     => HELPER_MYSQL_PORT,
                        'name'     => $this->helper->mysql(),
                        'username' => HELPER_MYSQL_USERNAME,
                        'password' => HELPER_MYSQL_PASSWORD
                    ]
                ]
            )
            ->case_full_installation($payload);
    }

    protected function case_full_installation($payload)
    {
        $this
            ->when(
                $configuration = CUT::createConfigurationFile(
                    $this->helper->configuration('server.json'),
                    [
                        'baseUrl'  => $payload->baseurl,
                        'database' => [
                            'driver'   => $payload->database->driver,
                            'host'     => $payload->database->host,
                            'port'     => $payload->database->port,
                            'name'     => $payload->database->name,
                            'username' => $payload->database->username,
                            'password' => $payload->database->password
                        ]
                    ]
                ),
                $configuration->database->dsn = $this->helper->sqlite()
            )
            ->then
                ->object($configuration)
                    ->isInstanceOf('Sabre\Katana\Configuration')

            ->when($database = CUT::createDatabase($configuration))
            ->then
                ->object($database)
                    ->isInstanceOf('Sabre\Katana\Database')

            ->when(
                $result = CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    $payload->login,
                    $payload->email,
                    $payload->password
                )
            )
            ->then
                ->boolean($result)
                    ->isTrue();
    }
}
