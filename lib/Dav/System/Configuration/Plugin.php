<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2016 fruux GmbH (https://fruux.com/)
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

namespace Sabre\Katana\Dav\System\Configuration;

use Sabre\Katana\Dav\System;
use Sabre\Katana\Configuration;
use Sabre\Katana\Exception;
use Sabre\DAV as SabreDav;
use Sabre\HTTP\RequestInterface as Request;
use Sabre\HTTP\ResponseInterface as Response;
use Hoa\Mail;
use Hoa\Socket;
use StdClass;

/**
 * The configuration plugin is responsible to manage sabre/katana's
 * configurations.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Plugin extends SabreDav\ServerPlugin {

    /**
     * sabre/katana's configuration.
     *
     * @var Configuration
     */
    protected $configuration = null;

    /**
     * Constructor.
     *
     * @param  Configuration  $configuration    Configuration.
     * @return void
     */
    function __construct(Configuration $configuration) {

        $this->configuration = $configuration;
    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using \Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'configuration';
    }

    /**
     * Returns a bunch of meta-data about the plugin.
     *
     * Providing this information is optional, and is mainly displayed by the
     * Browser plugin.
     *
     * The description key in the returned array may contain html and will not
     * be sanitized.
     *
     * @return array
     */
    function getPluginInfo() {

        return [
            'name'        => $this->getPluginName(),
            'description' => 'Read and write configurations of sabre/katana server.',
            'link'        => 'http://sabre.io/katana/'
        ];
    }

    /**
     * Use this method to tell the server this plugin defines additional
     * HTTP methods.
     *
     * This method is passed a URI. It should only return HTTP methods that are
     * available for the specified URI.
     *
     * @param  string $path
     * @return array
     */
    function getHTTPMethods($path) {

        if (System\Collection::NAME . '/' . Node::NAME !== $path) {
            return [];
        }

        return ['GET', 'POST'];
    }

    /**
     * This initializes the plugin.
     *
     * This function is called by Sabre\DAV\Server, after
     * addPlugin is called.
     *
     * This method should set up the required event subscriptions.
     *
     * @param  SabreDav\Server $server    Server.
     * @return void
     */
    function initialize(SabreDav\Server $server) {

        $server->on('method:GET',  [$this, 'httpGet']);
        $server->on('method:POST', [$this, 'httpPost']);
    }

    /**
     * Compute an HTTP GET method.
     *
     * @param  Request   $request     HTTP request.
     * @param  Response  $response    HTTP response.
     * @return bool
     * @throws Exception\Dav\Exception
     */
    function httpGet(Request $request, Response $response) {

        if (System\Collection::NAME . '/' . Node::NAME !== $request->getPath()) {
            return;
        }

        $queries = $request->getQueryParameters();

        if (isset($queries['test']) && 'mail' === $queries['test'] &&
            isset($queries['payload'])) {
            $payload = @json_decode($queries['payload']);

            if (!$payload ||
                !isset($payload->transport) ||
                !isset($payload->username) ||
                !isset($payload->password)) {
                throw new Exception\Dav\Exception(
                    'Payload is corrupted.'
                );
            }

            Mail\Message::setDefaultTransport(
                new Mail\Transport\Smtp(
                    new Socket\Client('tcp://' . $payload->transport),
                    $payload->username,
                    $payload->password
                )
            );

            $message            = new Mail\Message();
            $message['from']    = 'sabre/katana <' . $payload->username . '>';
            $message['to']      = $payload->username;
            $message['subject'] = 'Test mail from sabre/katana';

            $message->addContent(
                new Mail\Content\Text(
                    'Hey!' . "\n\n" .
                    'If you receive this email, it means that your ' .
                    'sabre/katana server is able to send emails! ' . "\n\n" .
                    'Congratulations :-).'
                )
            );

            $message->send();

            $response->setHeader('Content-Type', 'application/json');
            $response->setBody(json_encode(true));

            return false;
        }

        $configuration = [
            'database' => [
                'dsn'      => $this->configuration->database->dsn,
                'username' => $this->configuration->database->username,
            ],
            'mail' => [
                'address'  => '',
                'port'     => '',
                'username' => '',
                'password' => ''
            ]
        ];

        if (isset($this->configuration->mail)) {
            $socket = new Socket('tcp://' . $this->configuration->mail->transport);

            $configuration['mail']['address']  = $socket->getAddress();
            $configuration['mail']['port']     = $socket->hasPort() ? $socket->getPort() : 587;
            $configuration['mail']['username'] = $this->configuration->mail->username;
            $configuration['mail']['password'] = $this->configuration->mail->password;

            unset($socket);
        }

        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($configuration));

        return false;
    }

    /**
     * Compute an HTTP POST method.
     *
     * @param  Request   $request     HTTP request.
     * @param  Response  $response    HTTP response.
     * @return bool
     * @throws Exception\Dav\Exception
     */
    function httpPost(Request $request, Response $response) {

        if (System\Collection::NAME . '/' . Node::NAME !== $request->getPath()) {
            return;
        }

        $payload = @json_decode($request->getBodyAsString());

        if (!$payload ||
            !isset($payload->transport) ||
            !isset($payload->username) ||
            !isset($payload->password)) {
            throw new Exception\Dav\Exception(
                'Payload is corrupted.'
            );
        }

        $this->configuration->mail            = new StdClass();
        $this->configuration->mail->transport = $payload->transport;
        $this->configuration->mail->username  = $payload->username;
        $this->configuration->mail->password  = $payload->password;
        $this->configuration->save();

        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode(true));

        return false;
    }
}
