<?php

namespace Sabre\Katana\Server;

use Sabre\Katana\Configuration;
use Sabre\Katana\Database;
use Sabre\Katana\Exception;
use Sabre\HTTP\Request;
use Sabre\HTTP\Response;
use Hoa\Core\Core;
use Hoa\String\String;
use StdClass;
use PDOException;

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
     * Check the login.
     *
     * @param  string  $login    Login.
     * @return boolean
     */
    public static function checkLogin($login)
    {
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
    protected static function checkConfirmation($strings)
    {
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
    public static function checkPassword($passwords)
    {
        return static::checkConfirmation($passwords);
    }

    /**
     * Check the email matches a confirmed email and that it is not empty.
     *
     * @param  string  $emails    Emails.
     * @return boolean
     */
    public static function checkEmail($emails)
    {
        if (false === static::checkConfirmation($emails)) {
            return false;
        }

        $email = mb_substr($emails, 0, mb_strlen($emails) / 2);

        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
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
    public static function createConfigurationFile($filename, array $content)
    {
        if (!isset($content['baseUrl']) ||
            !isset($content['database']) ||
            empty($content['database']['driver']) ||
            !isset($content['database']['username']) ||
            !isset($content['database']['password'])) {
            throw new Exception\Installation(
                'Configuration content is corrupted. Expect at least ' .
                'a base URL, a database driver, username and password.'
            );
        }

        if ('mysql' === $content['database']['driver'] &&
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
                    sprintf(
                        'Unknown database %s.',
                        $content['database']['driver']
                    )
                );

        }

        $authentificationRealm = sha1(Core::uuid());

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
    public static function createDatabase(Configuration $configuration)
    {
        if (!isset($configuration->database)) {
            throw new Exception\Installation(
                'Configuration is corrupted, the database branch is missing.'
            );
        }

        try {
            $database = new Database(
                $configuration->database->dsn,
                $configuration->database->username,
                $configuration->database->password
            );
        } catch(PDOException $exception) {
            throw new Exception\Installation(
                'Cannot create the database.',
                0,
                $exception
            );
        }

        $templateSchemaIterator = $database->getTemplateSchemaIterator();

        try {
            foreach ($templateSchemaIterator as $templateSchema) {

                $schema = $templateSchema->open()->readAll();
                $database->exec($schema);
                $templateSchema->close();

            }
        } catch(PDOException $exception) {
            throw new Exception\Installation(
                'An error occured while setting up the database.',
                0,
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
    public static function createAdministratorProfile(
        Configuration $configuration,
        Database $database,
        $login,
        $email,
        $password
    )
    {
        if (false === isset($configuration->authentification)) {
            throw new Exception\Installation(
                'Configuration is corrupted, the authentification branch ' .
                'is missing.'
            );
        }

        if (false === static::checkLogin($login)) {
            throw new Exception\Installation('Login is invalid.');
        }

        if (false === static::checkEmail($email . $email)) {
            throw new Exception\Installation('Email is invalid.');
        }

        if (false === static::checkPassword($password . $password)) {
            throw new Exception\Installation('Password is invalid.');
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

        } catch(PDOException $exception) {
            throw new Exception\Installation(
                'An error occured while creating the administrator profile.',
                0,
                $exception
            );
        }

        return true;
    }
}
