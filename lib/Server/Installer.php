<?php

namespace Sabre\Katana\Server;

use Sabre\Katana\Configuration;
use Sabre\Katana\Exception;
use Sabre\HTTP\Request;
use Sabre\HTTP\Response;

/**
 * A set of utilities for the installer.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Installer
{
    /**
     * Check whether the application has been installed or not.
     *
     * @return boolean
     */
    public static function isInstalled()
    {
        return true === file_exists(Server::CONFIGURATION_FILE);
    }

    /**
     * Redirect to the home of the application.
     * This method does not send the response.
     *
     * @param  Response       $response         HTTP response.
     * @param  Configuration  $configuration    Configuration.
     * @return void
     */
    public static function redirectToIndex(Response $response, Configuration $configuration)
    {
        $response->setStatus(308);
        $response->setHeader('Location', $configuration->base_uri);
        $response->setBody(
            'The application is already installed. ' .
            'You are going to be redirected to the home.'
        );

        return;
    }

    /**
     * Redirect to the installation page of the application.
     * This method does not send the response.
     *
     * @param  Response  $response    HTTP response.
     * @param  Request   $request     HTTP request.
     * @return void
     */
    public static function redirectToInstall(Response $response, Request $request)
    {
        $response->setStatus(307);
        $response->setHeader('Location', $request->getBaseUrl() . 'install.php');
        $response->setBody(
            'The application is not installed. ' .
            'You are going to be redirected to the installation page.'
        );

        return;
    }

    /**
     * Check the base URL is syntactically correct.
     *
     * @param  string  $baseUrl    Base URL.
     * @return boolean
     */
    public static function checkBaseUrl($baseUrl)
    {
        return 0 !== preg_match('#^/(.+/)?$#', $baseUrl);
    }

    /**
     * Check the password matches a confirmed password and that it is not empty.
     *
     * @param  string  $passwords    Passwords (basically, both passwords
     *                               concatenated).
     * @return boolean
     */
    public static function checkPassword($passwords)
    {
        $length = mb_strlen($passwords);

        if (0 === $length || 0 !== ($length % 2)) {
            return false;
        }

        $halfLength = $length / 2;

        return
            mb_substr($passwords, 0, $halfLength)
            ===
            mb_substr($passwords, $halfLength);
    }

    /**
     * Create the configuration file.
     * The content must be of the form:
     *     [
     *         'baseUrl'  => …,
     *         'database' => [
     *             'type'     => …,
     *             'host'     => …, // if MySQL
     *             'port'     => …, // if MySQL
     *             'name'     => …, // if MySQL
     *             'username' => …,
     *             'password' => …
     *         ]
     *     ]
     * The configuration file will be saved before being returned.
     *
     * @param  string  $filename    Filename of the configuration file.
     * @param  array   $content     Configurations.
     * @return Configuration
     * @throw  Exception\Installation
     */
    public static function createConfigurationFile($filename, array $content)
    {
        if (!isset($content['baseUrl']) ||
            !isset($content['database']) ||
            empty($content['database']['type']) ||
            !isset($content['database']['username']) ||
            !isset($content['database']['password'])) {
            throw new Exception\Installation(
                'Configuration content is corrupted. Expect at least ' .
                'a base URL, a database type, username and password.'
            );
        }

        if ('mysql' === $content['database']['type'] &&
            (empty($content['database']['host']) ||
             empty($content['database']['port']) ||
             empty($content['database']['name']))) {
            throw new Exception\Installation(
                'Configuration content is corrupted for MySQL. Expect ' .
                'at least a host, a port and a name.'
            );
        }

        if (false === static::checkBaseUrl($content['baseUrl'])) {
            throw new Exception\Installation(
                sprintf(
                    'Base URL is not well-formed, given %s.',
                    $content['baseUrl']
                )
            );
        }

        switch ($content['database']['type']) {

            case 'mysql':
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s',
                    $content['database']['host'],
                    $content['database']['port'],
                    $content['database']['name']
                );
                break;

            case 'sqlite':
                $dsn = sprintf(
                    'sqlite:%s_%d.sqlite',
                    'katana://data/variable/database/katana',
                    time()
                );
                break;

            default:
                throw new Exception\Installation(
                    sprintf(
                        'Unknown database %s.',
                        $content['database']['type']
                    )
                );

        }

        $configuration           = new Configuration($filename, true);
        $configuration->base_url = $content['baseUrl'];
        $configuration->database = [
            'dsn'      => $dsn,
            'username' => $content['database']['username'],
            'password' => $content['database']['password']
        ];
        $configuration->save();

        return $configuration;
    }
}
