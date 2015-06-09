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
namespace Sabre\Katana\Test\Integration\Server;

use Sabre\Katana\Test\Integration\Suite;
use Sabre\Katana\Server\Installer as CUT;

/**
 * Test suite of the installer.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 *
 * @tags installation configuration database sqlite mysql authentication administration
 */
class Installer extends Suite {

    function case_full_installation_with_sqlite() {

        $this
            ->given(
                $payload = (object)[
                    'baseurl'  => '/katana/',
                    'email'    => 'gordon@freeman.hl',
                    'password' => '🔒 💩',
                    'database' => (object)[
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

    function case_full_installation_with_mysql() {

        $this
            ->given(
                $payload = (object)[
                    'baseurl'  => '/katana/',
                    'email'    => 'gordon@freeman.hl',
                    'password' => '🔒 💩',
                    'database' => (object)[
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

    protected function case_full_installation($payload) {

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
                    $payload->email,
                    $payload->password
                )
            )
            ->then
                ->boolean($result)
                    ->isTrue();
    }
}
