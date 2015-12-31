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

use PDO;
use Sabre\Katana\Configuration;
use Sabre\Katana\Exception;
use Sabre\Katana\DavAcl\User\Plugin as User;
use Sabre\HTTP\Request;
use Sabre\HTTP\Response;
use Sabre\Uri;
use Hoa\Iterator;
use Hoa\Ustring\Ustring;
use SplFileInfo;
use StdClass;
use PDOException;

/**
 * A set of utilities for the installer.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Installer {

    /**
     * Check whether the application has been installed or not.
     *
     * @return bool
     */
    static function isInstalled() {

        return true === file_exists(SABRE_KATANA_CONFIG);
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

        list($dirname) = Uri\split($request->getUrl());

        $response->setStatus(307);
        $response->setHeader('Location', $dirname . '/install.php');
        $response->setBody(
            'The application is not installed. ' .
            'You are going to be redirected to the installation page.'
        );
    }

    /**
     * Check if an existing directory is empty.
     * A directory is considered empty if it contains no file other than
     * `.empty` and `README.md`.
     *
     * @param  string  $directory    Directory.
     * @return bool
     */
    static function isDirectoryEmpty($directory) {

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
     * @return bool
     */
    static function checkBaseUrl($baseUrl) {

        return 0 !== preg_match('#^/(.+/)?$#', $baseUrl);
    }

    /**
     * Check the login.
     *
     * @param  string  $login    Login.
     * @return bool
     */
    static function checkLogin($login) {

        $string = new Ustring($login);
        return 0 < count($string);
    }

    /**
     * Check that a string matches a confirmed string and that it is not empty.
     * The argument is the raw concatenation of the two strings to compare.
     *
     * @param  string  $strings    Strings to check.
     * @return bool
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
     * @return bool
     */
    static function checkPassword($passwords) {

        return static::checkConfirmation($passwords);
    }

    /**
     * Check the email matches a confirmed email and that it is not empty.
     *
     * @param  string  $emails    Emails.
     * @return bool
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
     * @return bool
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

        if (false === in_array($parameters['driver'], PDO::getAvailableDrivers())) {
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
                $database = new PDO(
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

            $statement = $database->prepare(
                'SELECT COUNT(DISTINCT table_name) ' .
                'FROM information_schema.columns ' .
                'WHERE table_schema = :database'
            );
            $statement->execute([
                'database' => $parameters['name']
            ]);

            if (0 < $statement->fetchColumn()) {
                throw new Exception\Installation(
                    'Database `%s` is not empty. An empty database is ' .
                    'required to install sabre/katana.',
                    3,
                    $parameters['name']
                );
            }
        } else {
            throw new Exception\Installation(
                'Unknown database %s.',
                4,
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
    static function createConfigurationFile($filename, array $content) {

        if (!isset($content['baseUrl']) ||
            !isset($content['database']) ||
            empty($content['database']['driver']) ||
            !isset($content['database']['username']) ||
            !isset($content['database']['password'])) {
            throw new Exception\Installation(
                'Configuration content is corrupted. Expect at least ' .
                'a base URL, a database driver, username and password.',
                5
            );
        }

        if ('mysql' === $content['database']['driver'] &&
            (empty($content['database']['host']) ||
             empty($content['database']['port']) ||
             empty($content['database']['name']))) {
            throw new Exception\Installation(
                'Configuration content is corrupted for MySQL. Expect ' .
                'at least a host, a port and a name.',
                6
            );
        }

        if (false === static::checkBaseUrl($content['baseUrl'])) {
            throw new Exception\Installation(
                'Base URL is not well-formed, given %s.',
                6,
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
                    SABRE_KATANA_PREFIX . '/data/database/katana',
                    time()
                );
                break;

            default:
                throw new Exception\Installation(
                    'Unknown database %s.',
                    7,
                    $content['database']['driver']
                );
        }

        touch($filename);

        $configuration                     = new Configuration($filename, true);
        $configuration->base_url           = $content['baseUrl'];
        $configuration->database           = new StdClass();
        $configuration->database->dsn      = $dsn;
        $configuration->database->username = $content['database']['username'];
        $configuration->database->password = $content['database']['password'];
        $configuration->save();

        return $configuration;
    }

    /**
     * Create the database.
     *
     * @param  Configuration  $configuration    Configuration.
     * @return PDO
     * @throw  Exception\Installation
     */
    static function createDatabase(Configuration $configuration) {

        if (!isset($configuration->database)) {
            throw new Exception\Installation(
                'Configuration is corrupted, the database branch is missing.',
                8
            );
        }

        try {
            $database = new PDO(
                $configuration->database->dsn,
                $configuration->database->username,
                $configuration->database->password
            );
        } catch (PDOException $exception) {
            throw new Exception\Installation(
                'Cannot create the database.',
                9,
                null,
                $exception
            );
        }

        $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);
        $templateSchemaIterator = glob(SABRE_KATANA_PREFIX . '/resource/default/database/*.' . $driver . '.sql');

        try {
            foreach ($templateSchemaIterator as $templateSchema) {

                $schema  = file_get_contents($templateSchema);
                $verdict = $database->exec($schema);

                if (false === $verdict) {
                    throw new PDOException(
                        'Unable to execute the following schema:' . "\n" .
                        $schema,
                        10
                    );
                }

            }
        } catch (PDOException $exception) {
            throw new Exception\Installation(
                'An error occured while setting up the database.',
                11,
                null,
                $exception
            );
        }

        return $database;
    }

    /**
     * Create the administrator profile.
     *
     * @param Configuration $configuration
     * @param PDO $database
     * @param  string         $email            Administrator's email.
     * @param  string         $password         Administrator's password.
     * @return bool
     * @throw  Exception\Installation
     */
    static function createAdministratorProfile(
        Configuration $configuration,
        PDO $database,
        $email,
        $password
    ) {

        $login = Server::ADMINISTRATOR_LOGIN;

        if (false === static::checkLogin($login)) {
            throw new Exception\Installation('Login is invalid.', 13);
        }

        if (false === static::checkEmail($email . $email)) {
            throw new Exception\Installation('Email is invalid.', 14);
        }

        if (false === static::checkPassword($password . $password)) {
            throw new Exception\Installation('Password is invalid.', 15);
        }

        $digest = User::hashPassword($password);

        try {
            $statement = $database->prepare(
                'INSERT INTO principals (uri, email, displayname) ' .
                'VALUES (:uri, :email, :displayname)'
            );
            $statement->execute([
                'uri'         => 'principals/' . $login,
                'email'       => $email,
                'displayname' => 'Administrator'
            ]);
            $statement->execute([
                'uri'         => 'principals/' . $login . '/calendar-proxy-read',
                'email'       => null,
                'displayname' => null
            ]);
            $statement->execute([
                'uri'         => 'principals/' . $login . '/calendar-proxy-write',
                'email'       => null,
                'displayname' => null
            ]);

            $statement = $database->prepare(
                'INSERT INTO users (username, digesta1) ' .
                'VALUES (:username, :digest)'
            );
            $statement->execute([
                'username' => $login,
                'digest'   => $digest
            ]);
        } catch (PDOException $exception) {
            throw new Exception\Installation(
                'An error occured while creating the administrator profile.',
                16,
                null,
                $exception
            );
        }

        return true;
    }
}
