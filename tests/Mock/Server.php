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

namespace Mock;

use Sabre\Katana\Server\Server as OriginalServer;
use Sabre\Katana\Configuration;
use Sabre\Katana\Server\Installer;
use Sabre\Katana\Test\Helper;

/**
 * Mock of Sabre\Katana\Server\Server.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Server extends OriginalServer {

    const ADMINISTRATOR_EMAIL    = 'katana@domain.tld';
    const ADMINISTRATOR_PASSWORD = '🔒';

    public $request  = null;
    public $response = null;

    protected function initializeServer() {

        parent::initializeServer();

        $server         = $this->getServer();
        $server->sapi   = new Sapi();
        $this->request  = &$server->httpRequest;
        $this->response = &$server->httpResponse;
    }

    protected function initializeConfiguration() {

        $configuration        = new Helper\Configuration();
        $sqlite               = new Helper\SQLite();
        $this->configuration = new Configuration(
            $configuration(
                'configuration.json',
                [
                    'base_url' => '/',
                    'database' => [
                        'dsn'      => $sqlite(true),
                        'username' => '',
                        'password' => ''
                    ]
                ]
            )
        );
    }

    protected function initializeDatabase() {

        $database = Installer::createDatabase($this->getConfiguration());
        Installer::createAdministratorProfile(
            $this->getConfiguration(),
            $database,
            static::ADMINISTRATOR_EMAIL,
            static::ADMINISTRATOR_PASSWORD
        );
        unset($database);

        return parent::initializeDatabase();
    }
}
