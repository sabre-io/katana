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

namespace Sabre\Katana\Server;

use Sabre\Katana\Configuration;
use Sabre\Katana\Database;
use Sabre\Katana\Exception;
use Sabre\HTTP\Request;
use Sabre\HTTP\Response;
use Hoa\Core;
use Hoa\Iterator;
use Hoa\String\String;
use SplFileInfo;
use StdClass;
use PDOException;

/**
 * A set of utilities for the installer.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Installer {

    /**
     * Check whether the application has been installed or not.
     *
     * @return boolean
     */
    static function isInstalled() {

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
    static function redirectToIndex(Response $response, Configuration $configuration) {

        $response->setStatus(308);
        $response->setHeader('Location', $configuration->base_url);
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
    static function redirectToInstall(Response $response, Request $request) {

        $response->setStatus(307);
        $response->setHeader('Location', $request->getBaseUrl() . 'install.php');
        $response->setBody(
            'The application is not installed. ' .
            'You are going to be redirected to the installation page.'
        );

        return;
    }

    /**
     * Check if an existing directory is empty.
     * A directory is considered empty if it contains no file other than
     * `.empty` and `README.md`.
     *
     * @param  string  $directory    Directory.
     * @return boolean
     */
    public static function isDirectoryEmpty($directory)
    {
        $iterator = new Iterator\CallbackFilter(
            new Iterator\FileSystem($directory),
            function(SplFileInfo $current) {
                return !in_array(
                    $current->getFileName(),
                    [
                        '.empty',
                        'README.md'
                    ]
                );
            }
        );
        $iterator->rewind();

        return false === $iterator->valid();
    }

    /**
     * Check the base URL is syntactically correct.
     *
     * @param  string  $baseUrl    Base URL.
     * @return boolean
     */
    static function checkBaseUrl($baseUrl) {

        return 0 !== preg_match('#^/(.+/)?$#', $baseUrl);
    }

    /**
     * Check the login.
     *
     * @param  string  $login    Login.
     * @return boolean
     */
    static function checkLogin($login) {

        $string = new String($login);
        return 0 < count($string);
    }

    /**
     * Check that a string matches a confirmed string and that it is not empty.
     * The argument is the raw concatenation of the two strings to compare.
     *
     * @param  string  $strings    Strings to check.
     * @return boolean
     */
    protected static function checkConfirmation($strings) {

        $length = mb_strlen($strings);

        if (0 === $length || 0 !== ($length % 2)) {
            return false;
        }

        $halfLength = $length / 2;

        return
            mb_substr($strings, 0, $halfLength)
            ===
            mb_substr($strings, $halfLength);
    }

    /**
     * Check the password matches a confirmed password and that it is not empty.
     *
     * @param  string  $passwords    Passwords.
     * @return boolean
     */
    static function checkPassword($passwords) {

        return static::checkConfirmation($passwords);
    }

    /**
     * Check the email matches a confirmed email and that it is not empty.
     *
     * @param  string  $emails    Emails.
     * @return boolean
     */
    static function checkEmail($emails) {

        if (false === static::checkConfirmation($emails)) {
            return false;
        }

        $email = mb_substr($emails, 0, mb_strlen($emails) / 2);

        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check the database parameters are correct.
     * The expected parameters are:
     *     [
     *         'driver'   => …,
     *         'host'     => …,
     *         'port'     => …,
     *         'name'     => …,
     *         'username' => …,
     *         'password' => …
     *     ]
     *
     * @param  array $parameters    Parameters.
     * @return boolean
     * @throw  Exception\Installation
     */
    static function checkDatabase(array $parameters) {

        if (empty($parameters['driver']) ||
            !isset($parameters['host']) ||
            !isset($parameters['port']) ||
            !isset($parameters['name']) ||
            !isset($parameters['username']) ||
            !isset($parameters['password'])) {
            throw new Exception\Installation(
                'Database parameters are corrupted. Expect a driver, a host, ' .
                'a port, a name, a username and a password.',
                0
            );
        }

        if (false === in_array($parameters['driver'], Database::getAvailableDrivers())) {
            throw new Exception\Installation(
                'Driver %s is not supported by the server.',
                1,
                $parameters['driver']
            );
        }

        if ('sqlite' === $parameters['driver']) {
            // Nothing.
        } elseif ('mysql' === $parameters['driver']) {

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s',
                $parameters['host'],
                $parameters['port'],
                $parameters['name']
            );

            try {

                $database = new Database(
                    $dsn,
                    $parameters['username'],
                    $parameters['password']
                );

            } catch (PDOException $exception) {
                throw new Exception\Installation(
                    'Cannot connect to the database.',
                    2,
                    null,
                    $exception
                );
            }

        } else {
            throw new Exception\Installation(
                'Unknown database %s.',
                3,
                $parameters['driver']
            );
        }

        return true;
    }

    /**
     * Create the configuration file.
     * The content must be of the form:
     *     [
     *         'baseUrl'  => …,
     *         'database' => [
     *             'driver'   => …,
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
    static function createConfigurationFile($filename, array $content)
    {
        if (!isset($content['baseUrl']) ||
            !isset($content['database']) ||
            empty($content['database']['driver']) ||
            !isset($content['database']['username']) ||
            !isset($content['database']['password'])) {
            throw new Exception\Installation(
                'Configuration content is corrupted. Expect at least ' .
                'a base URL, a database driver, username and password.',
                4
            );
        }

        if ('mysql' === $content['database']['driver'] &&
            (empty($content['database']['host']) ||
             empty($content['database']['port']) ||
             empty($content['database']['name']))) {
            throw new Exception\Installation(
                'Configuration content is corrupted for MySQL. Expect ' .
                'at least a host, a port and a name.',
                5
            );
        }

        if (false === static::checkBaseUrl($content['baseUrl'])) {
            throw new Exception\Installation(
                'Base URL is not well-formed, given %s.',
                5,
                $content['baseUrl']
            );
        }

        switch ($content['database']['driver']) {

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
                    'Unknown database %s.',
                    6,
                    $content['database']['driver']
                );

        }

        $authentificationRealm = sha1(Core::uuid());
        touch($filename);

        $configuration                          = new Configuration($filename, true);
        $configuration->base_url                = $content['baseUrl'];
        $configuration->authentification        = new StdClass();
        $configuration->authentification->realm = $authentificationRealm;
        $configuration->database                = new StdClass();
        $configuration->database->dsn           = $dsn;
        $configuration->database->username      = $content['database']['username'];
        $configuration->database->password      = $content['database']['password'];
        $configuration->save();

        return $configuration;
    }

    /**
     * Create the database.
     *
     * @param  Configuration  $configuration    Configuration.
     * @return Database
     * @throw  Exception\Installation
     */
    static function createDatabase(Configuration $configuration)
    {
        if (!isset($configuration->database)) {
            throw new Exception\Installation(
                'Configuration is corrupted, the database branch is missing.',
                7
            );
        }

        try {
            $database = new Database(
                $configuration->database->dsn,
                $configuration->database->username,
                $configuration->database->password
            );
        } catch (PDOException $exception) {
            throw new Exception\Installation(
                'Cannot create the database.',
                8,
                null,
                $exception
            );
        }

        $templateSchemaIterator = $database->getTemplateSchemaIterator();

        try {
            foreach ($templateSchemaIterator as $templateSchema) {

                $schema  = $templateSchema->open()->readAll();
                $verdict = $database->exec($schema);

                if (false === $verdict) {
                    throw new PDOException(
                        'Unable to execute the following schema:' . "\n" . '%s',
                        9,
                        $schema
                    );
                }

                $templateSchema->close();

            }
        } catch (PDOException $exception) {
            throw new Exception\Installation(
                'An error occured while setting up the database.',
                10,
                null,
                $exception
            );
        }

        return $database;
    }

    /**
     * Create the administrator profile.
     *
     * @param  Configuration  $configuration    Configuration.
     * @param  Database       $database         Database.
     * @param  string         $login            Administrator's login.
     * @param  string         $email            Administrator's email.
     * @param  string         $password         Administrator's password.
     * @return boolean
     * @throw  Exception\Installation
     */
    static function createAdministratorProfile(
        Configuration $configuration,
        Database $database,
        $login,
        $email,
        $password
    ) {
        if (false === isset($configuration->authentification)) {
            throw new Exception\Installation(
                'Configuration is corrupted, the authentification branch ' .
                'is missing.',
                11
            );
        }

        if (false === static::checkLogin($login)) {
            throw new Exception\Installation('Login is invalid.', 12);
        }

        if (false === static::checkEmail($email . $email)) {
            throw new Exception\Installation('Email is invalid.', 13);
        }

        if (false === static::checkPassword($password . $password)) {
            throw new Exception\Installation('Password is invalid.', 14);
        }

        $realm  = $configuration->authentification->realm;
        $digest = md5($login . ':' . $realm . ':' . $password);

        try {

            $statement = $database->prepare(
                'INSERT INTO principals (uri, email, displayname) ' .
                'VALUES (:uri, :email, :displayname)'
            );
            $statement->execute([
                'uri'         => 'principals/admin',
                'email'       => $email,
                'displayname' => 'Administrator'
            ]);
            $statement->execute([
                'uri'         => 'principals/admin/calendar-proxy-read',
                'email'       => null,
                'displayname' => null
            ]);
            $statement->execute([
                'uri'         => 'principals/admin/calendar-proxy-write',
                'email'       => null,
                'displayname' => null
            ]);

            $statement = $database->prepare(
                'INSERT INTO users (username, digesta1) '.
                'VALUES (:username, :digest)'
            );
            $statement->execute([
                'username' => $login,
                'digest'   => $digest
            ]);

        } catch (PDOException $exception) {
            throw new Exception\Installation(
                'An error occured while creating the administrator profile.',
                15,
                null,
                $exception
            );
        }

        return true;
    }
}
