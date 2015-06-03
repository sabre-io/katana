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
namespace Sabre\Katana\Test\Unit\Dav\System;

use Sabre\Katana\Test\Unit\Suite;
use Mock;

/**
 * Test suite of the configuration system.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Configuration extends Suite {

    function case_get() {

        $this
            ->given(
                $server = new Mock\Server(),
                $server->request->setMethod('GET'),
                $server->request->setURL('/system/configurations'),
                $server->request->addHeader(
                    'Authorization',
                    'Basic ' .
                    base64_encode(
                        $server::ADMINISTRATOR_LOGIN .
                        ':' .
                        $server::ADMINISTRATOR_PASSWORD
                    )
                )
            )
            ->when($server->run())
            ->then
                ->string($server->response->getHeader('Content-Type'))
                    ->isEqualTo('application/json')
                ->let($body = json_decode($server->response->getBodyAsString(), true))
                ->array($body)
                    ->hasKey('database')
                    ->hasKey('mail')
                    ->hasSize(2)
                ->array($body['database'])
                    ->hasKey('dsn')
                    ->hasKey('username')
                    ->hasSize(2)
                ->array($body['mail'])
                    ->hasKey('address')
                    ->hasKey('port')
                    ->hasKey('username')
                    ->hasKey('password')
                    ->hasSize(4);
    }

    function case_post() {

        $this
            ->given($server = new Mock\Server())
            ->when(
                $configuration                = $server->getConfiguration(),
                $configuration->database->dsn = 'sqlite:/…'
            )
            ->then
                ->object($configuration->jsonSerialize())
                    ->isEqualTo(
                        (object)[
                            'base_url' => '/',
                            'database' => (object)[
                                'dsn'      => 'sqlite:/…',
                                'username' => '',
                                'password' => ''
                            ]
                        ]
                    )

            ->given(
                $server->request->setMethod('POST'),
                $server->request->setURL('/system/configurations'),
                $server->request->addHeader(
                    'Authorization',
                    'Basic ' .
                    base64_encode(
                        $server::ADMINISTRATOR_LOGIN .
                        ':' .
                        $server::ADMINISTRATOR_PASSWORD
                    )
                ),
                $server->request->setBody(
                    json_encode([
                        'transport' => 'foo.bar:587',
                        'username'  => 'alix',
                        'password'  => '💩'
                    ])
                )
            )
            ->when(
                $server->run(),
                $configuration = $server->getConfiguration()
            )
            ->then
                ->object($configuration->jsonSerialize())
                    ->isEqualTo(
                        (object)[
                            'base_url' => '/',
                            'database' => (object)[
                                'dsn'      => 'sqlite:/…',
                                'username' => '',
                                'password' => ''
                            ],
                            'mail' => (object)[
                                'transport' => 'foo.bar:587',
                                'username'  => 'alix',
                                'password'  => '💩'
                            ]
                        ]
                    );
    }
}
