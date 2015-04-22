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

namespace Sabre\Katana\Test\Unit\Server;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Server\Installer as CUT;
use Sabre\Katana\Server\Server;
use Sabre\Katana\Configuration;
use Sabre\Katana\Database;
use Sabre\Katana\DavAcl\User\Plugin as User;
use Sabre\HTTP;

/**
 * Test suite of the installer.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 *
 * @tags installation
 */
class Installer extends Suite
{
    protected $_defaultConfiguration = [
        'baseUrl'          => '/',
        'database' => [
            'driver'   => 'sqlite',
            'username' => '',
            'password' => ''
        ]
    ];

    public function case_is_installed()
    {
        $this
            ->given($this->function->file_exists = true)
            ->when($result = CUT::isInstalled())
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_is_not_installed()
    {
        $this
            ->given($this->function->file_exists = false)
            ->when($result = CUT::isInstalled())
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    /**
     * @tags installation http
     */
    public function case_redirect_to_index()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'server.json',
                        ['base_url' => '/mybase/']
                    )
                ),
                $response = new HTTP\Response()
            )
            ->when($result = CUT::redirectToIndex($response, $configuration))
            ->then
                ->variable($result)
                    ->isNull()
                ->object($response)
                ->integer($response->getStatus())
                    ->isEqualTo(308)
                ->string($response->getHeader('Location'))
                    ->isEqualTo('/mybase/')
                ->string($response->getBody())
                    ->isNotEmpty();
    }

    /**
     * @tags installation http
     */
    public function case_redirect_to_install()
    {
        $this
            ->given(
                $request  = new HTTP\Request(null, '/mybase/foo'),
                $response = new HTTP\Response()
            )
            ->when($result = CUT::redirectToInstall($response, $request))
            ->then
                ->variable($result)
                    ->isNull()
                ->object($response)
                ->integer($response->getStatus())
                    ->isEqualTo(307)
                ->string($response->getHeader('Location'))
                    ->isEqualTo('/mybase/install.php')
                ->string($response->getBody())
                    ->isNotEmpty();
    }

    public function case_check_correct_base_url()
    {
        $this
            ->given($_baseUrl = $this->realdom->regex('#^/(.+/)?$#'))
            ->when(function() use($_baseUrl) {
                foreach ($this->realdom->sampleMany($_baseUrl, 100) as $baseUrl) {
                    $this
                        ->boolean($result = CUT::checkBaseUrl($baseUrl))
                            ->isTrue();
                }
            });
    }

    public function case_check_incorrect_base_url()
    {
        $this
            ->given($_baseUrl = $this->realdom->regex('#[^/].+[^/]$#'))
            ->when(function() use($_baseUrl) {
                foreach ($this->realdom->sampleMany($_baseUrl, 100) as $baseUrl) {
                    $this
                        ->boolean($result = CUT::checkBaseUrl($baseUrl))
                            ->isFalse();
                }
            });
    }

    /**
     * @tags installation authentication
     */
    public function case_check_correct_login()
    {
        $this
            ->given($login = '💩')
            ->when($result = CUT::checkLogin($login))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    /**
     * @tags installation authentication
     */
    public function case_check_incorrect_login()
    {
        $this
            ->given($login = '')
            ->when($result = CUT::checkLogin($login))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    /**
     * @tags installation authentication
     */
    public function case_check_correct_password()
    {
        $this
            ->given($_password = $this->realdom->regex('#[\w\d_!\-@💩]+#'))
            ->when(function() use($_password) {
                foreach ($this->realdom->sampleMany($_password, 100) as $password) {
                    $this
                        ->given($passwords = $password . $password)
                        ->boolean($result = CUT::checkPassword($passwords))
                            ->isTrue();
                }
            });
    }

    /**
     * @tags installation authentication
     */
    public function case_check_incorrect_empty_password()
    {
        $this
            ->given($password = '')
            ->when($result = CUT::checkPassword($password . $password))
            ->then
                ->boolean($result)
                    ->isFalse()

            ->given($password = null)
            ->when($result = CUT::checkPassword($password . $password))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    /**
     * @tags installation authentication
     */
    public function case_check_incorrect_unmatched_password()
    {
        $this
            ->given(
                $passwords = [
                    ['a', 'b'],
                    ['a', 'aa'],
                    ['💩', '____']
                ]
            )
            ->when(function() use($passwords){
                foreach ($passwords as $pair) {
                    list($password, $confirmed) = $pair;
                    $this
                        ->given($result = CUT::checkPassword($password . $confirmed))
                        ->boolean($result)
                            ->isFalse();
                }
            });
    }

    /**
     * @tags installation authentication
     */
    public function case_check_correct_email()
    {
        $this
            ->given($_email = $this->realdom->regex('#\w[\w\d\-_]+[\w\d]@[a-zA-Z\d]\.(com|net|org)#'))
            ->when(function() use($_email) {
                foreach ($this->realdom->sampleMany($_email, 100) as $email) {
                    $this
                        ->given($emails = $email . $email)
                        ->boolean($result = CUT::checkEmail($emails))
                            ->isTrue();
                }
            });
    }

    /**
     * @tags installation authentication
     */
    public function case_check_incorrect_empty_email()
    {
        $this
            ->given($email = '')
            ->when($result = CUT::checkEmail($email . $email))
            ->then
                ->boolean($result)
                    ->isFalse()

            ->given($email = null)
            ->when($result = CUT::checkEmail($email . $email))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    /**
     * @tags installation authentication
     */
    public function case_check_incorrect_unmatched_email()
    {
        $this
            ->given(
                $emails = [
                    ['a', 'b'],
                    ['a', 'aa'],
                    ['💩', '____']
                ]
            )
            ->when(function() use($emails){
                foreach ($emails as $pair) {
                    list($email, $confirmed) = $pair;
                    $this
                        ->given($result = CUT::checkPassword($email . $confirmed))
                        ->boolean($result)
                            ->isFalse();
                }
            });
    }

    /**
     * @tags installation database
     */
    public function case_check_database_driver_is_empty()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => '',
                    'host'     => '',
                    'port'     => '',
                    'name'     => '',
                    'username' => '',
                    'password' => ''
                ]
            )
            ->exception(function() use ($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation database
     */
    public function case_check_database_host_is_required()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => '',
                    'port'     => '',
                    'name'     => '',
                    'username' => '',
                    'password' => ''
                ]
            )
            ->exception(function() use ($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation database
     */
    public function case_check_database_port_is_required()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => '',
                    'host'     => '',
                    'name'     => '',
                    'username' => '',
                    'password' => ''
                ]
            )
            ->exception(function() use ($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation database
     */
    public function case_check_database_name_is_required()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => '',
                    'host'     => '',
                    'port'     => '',
                    'username' => '',
                    'password' => ''
                ]
            )
            ->exception(function() use ($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation database
     */
    public function case_check_database_username_is_required()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => '',
                    'host'     => '',
                    'port'     => '',
                    'name'     => '',
                    'password' => ''
                ]
            )
            ->exception(function() use ($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation database
     */
    public function case_check_database_password_is_required()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => '',
                    'host'     => '',
                    'port'     => '',
                    'name'     => '',
                    'username' => ''
                ]
            )
            ->exception(function() use ($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation database
     */
    public function case_check_database_unavailable_driver()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => 'katana',
                    'host'     => '',
                    'port'     => '',
                    'name'     => '',
                    'username' => '',
                    'password' => ''
                ]
            )
            ->exception(function() use ($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation')
                ->hasMessage('Driver katana is not supported by the server.');
    }

    /**
     * @tags installation database sqlite
     */
    public function case_check_database_sqlite()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => 'sqlite',
                    'host'     => '',
                    'port'     => '',
                    'name'     => '',
                    'username' => '',
                    'password' => ''
                ]
            )
            ->when($result = CUT::checkDatabase($parameters))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    /**
     * @tags installation database mysql
     */
    public function case_check_database_mysql_bad_parameters()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => 'mysql',
                    'host'     => 'localhost',
                    'port'     => '1',
                    'name'     => 'bar',
                    'username' => 'gordon',
                    'password' => '🔒 🔒 🔒'
                ]
            )
            ->exception(function() use($parameters) {
                CUT::checkDatabase($parameters);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation database mysql
     */
    public function case_check_database_mysql()
    {
        $this
            ->given(
                $parameters = [
                    'driver'   => 'mysql',
                    'host'     => HELPER_MYSQL_HOST,
                    'port'     => HELPER_MYSQL_PORT,
                    'name'     => $this->helper->mysql(),
                    'username' => HELPER_MYSQL_USERNAME,
                    'password' => HELPER_MYSQL_PASSWORD
                ]
            )
            ->when($result = CUT::checkDatabase($parameters))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    /**
     * @tags installation configuration
     */
    public function case_create_configuration_file()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver']   = 'sqlite',
                $content['database']['username'] = 'foo',
                $content['database']['password'] = 'bar'
            )
            ->and
                ->string(file_get_contents($filename))
                    ->isEmpty()
            ->when($result = CUT::createConfigurationFile($filename, $content))
            ->then
                ->object($result)
                    ->isInstanceOf('Sabre\Katana\Configuration')

                ->string($jsonContent = file_get_contents($filename))
                    ->isNotEmpty()

                ->array($content = json_decode($jsonContent, true))
                    ->hasKey('base_url')
                    ->hasKey('database')
                ->array($content['database'])
                    ->hasKey('dsn')
                    ->hasKey('username')
                    ->hasKey('password')

                ->string($content['base_url'])
                    ->isEqualTo('/')
                ->string($content['database']['dsn'])
                    ->matches('#^sqlite:katana://data/variable/database/katana_\d+\.sqlite#')
                ->string($content['database']['username'])
                    ->isEqualTo('foo')
                ->string($content['database']['password'])
                    ->isEqualTo('bar');
    }

    /**
     * @tags installation configuration
     */
    public function case_create_configuration_file_base_url_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $this->remove($content, 'baseUrl')
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration
     */
    public function case_create_configuration_file_base_url_is_not_well_formed()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['baseUrl'] = 'a'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database
     */
    public function case_create_configuration_file_database_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $this->remove($content, 'database')
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database
     */
    public function case_create_configuration_file_database_driver_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $this->remove($content, 'database', 'driver')
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database
     */
    public function case_create_configuration_file_database_driver_is_empty()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = ''
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database
     */
    public function case_create_configuration_file_database_username_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $this->remove($content, 'database', 'username')
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database
     */
    public function case_create_configuration_file_database_password_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $this->remove($content, 'database', 'password')
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_configuration_file_database_mysql_host_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'mysql',
                $content['database']['port']   = '42',
                $content['database']['name']   = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_configuration_file_database_mysql_empty_host()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'mysql',
                $content['database']['host']   = '',
                $content['database']['port']   = '42',
                $content['database']['name']   = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_configuration_file_database_mysql_port_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'mysql',
                $content['database']['host']   = 'foo',
                $content['database']['name']   = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_configuration_file_database_mysql_empty_port()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'mysql',
                $content['database']['host']   = 'foo',
                $content['database']['port']   = '',
                $content['database']['name']   = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_configuration_file_database_mysql_name_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'mysql',
                $content['database']['host']   = 'foo',
                $content['database']['port']   = '42'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_configuration_file_database_mysql_empty_name()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'mysql',
                $content['database']['host']   = 'foo',
                $content['database']['port']   = '42',
                $content['database']['name']   = ''
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database
     */
    public function case_create_configuration_file_database_unknown_driver()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'crazydb'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database sqlite
     */
    public function case_create_configuration_file_database_sqlite_dsn()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'sqlite'
            )
            ->when($result = CUT::createConfigurationFile($filename, $content))
            ->then
                ->string($result->database->dsn)
                    ->matches('#^sqlite:katana://data/variable/database/katana_\d+\.sqlite#');
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_configuration_file_database_mysql_dsn()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['driver'] = 'mysql',
                $content['database']['host']   = 'foo',
                $content['database']['port']   = '42',
                $content['database']['name']   = 'bar'
            )
            ->when($result = CUT::createConfigurationFile($filename, $content))
            ->then
                ->string($result->database->dsn)
                    ->isEqualTo('mysql:host=foo;port=42;dbname=bar');
    }

    /**
     * @tags installation configuration database sqlite
     */
    public function case_create_sqlite_database()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'database' => [
                                'dsn'      => $this->helper->sqlite(),
                                'username' => '',
                                'password' => ''
                            ]
                        ]
                    )
                )
            )
            ->when($result = CUT::createDatabase($configuration))
            ->then
                ->object($result)
                    ->isInstanceOf('Sabre\Katana\Database');

        $this
            ->when(
                $result = $result->query(
                    'SELECT name ' .
                    'FROM sqlite_master ' .
                    'WHERE type="table" ' .
                    'ORDER BY name ASC',
                    $result::FETCH_COLUMN,
                    0
                )
            )
            ->then
                ->array(iterator_to_array($result))
                    ->isEqualTo([
                        'addressbookchanges',
                        'addressbooks',
                        'calendarchanges',
                        'calendarobjects',
                        'calendars',
                        'calendarsubscriptions',
                        'cards',
                        'groupmembers',
                        'locks',
                        'principals',
                        'propertystorage',
                        'schedulingobjects',
                        'users'
                    ]);
    }

    /**
     * @tags installation configuration database mysql
     */
    public function case_create_mysql_database()
    {
        $this
            ->given(
                $databaseName  = $this->helper->mysql(),
                $dsn           = HELPER_MYSQL_DSN . ';dbname=' . $databaseName,
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'database' => [
                                'dsn'      => $dsn,
                                'username' => HELPER_MYSQL_USERNAME,
                                'password' => HELPER_MYSQL_PASSWORD
                            ]
                        ]
                    )
                )
            )
            ->when($result = CUT::createDatabase($configuration))
            ->then
                ->object($result)
                    ->isInstanceOf('Sabre\Katana\Database');

        $this
            ->when(
                $result = $result->query(
                    'SHOW TABLES',
                    $result::FETCH_COLUMN,
                    0
                )
            )
            ->then
                ->array(iterator_to_array($result))
                    ->isEqualTo([
                        'addressbookchanges',
                        'addressbooks',
                        'calendarchanges',
                        'calendarobjects',
                        'calendars',
                        'calendarsubscriptions',
                        'cards',
                        'groupmembers',
                        'locks',
                        'principals',
                        'propertystorage',
                        'schedulingobjects',
                        'users'
                    ]);
    }

    /**
     * @tags installation configuration database
     */
    public function case_create_database_broken_configuration()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        []
                    )
                )
            )
            ->exception(function() use($configuration) {
                CUT::createDatabase($configuration);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    /**
     * @tags installation configuration database sqlite authentication administration
     */
    public function case_create_administrator_profile()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'database' => [
                                'dsn'      => $this->helper->sqlite(),
                                'username' => '',
                                'password' => ''
                            ]
                        ]
                    )
                ),
                $database = CUT::createDatabase($configuration),
                $login    = Server::ADMINISTRATOR_LOGIN,
                $email    = 'gordon@freeman.hl',
                $password = '💩'
            )
            ->when(
                $result = CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    $email,
                    $password
                )
            )
            ->then
                ->boolean($result)
                    ->isTrue();

        $this
            ->when(
                $result = $database->query(
                    'SELECT * FROM principals',
                    $database::FETCH_CLASS,
                    'StdClass'
                )
            )
            ->then
                ->array($collection = iterator_to_array($result))
                    ->hasSize(3)

                ->let($tuple = $collection[0])
                ->string($tuple->id)
                    ->isEqualTo('1')
                ->string($tuple->uri)
                    ->isEqualTo('principals/' . $login)
                ->string($tuple->email)
                    ->isEqualTo($email)
                ->string($tuple->displayname)
                    ->isEqualTo('Administrator')

                ->let($tuple = $collection[1])
                ->string($tuple->id)
                    ->isEqualTo('2')
                ->string($tuple->uri)
                    ->isEqualTo('principals/' . $login . '/calendar-proxy-read')
                ->variable($tuple->email)
                    ->isNull()
                ->variable($tuple->displayname)
                    ->isNull()

                ->let($tuple = $collection[2])
                ->string($tuple->id)
                    ->isEqualTo('3')
                ->string($tuple->uri)
                    ->isEqualTo('principals/' . $login . '/calendar-proxy-write')
                ->variable($tuple->email)
                    ->isNull()
                ->variable($tuple->displayname)
                    ->isNull()

            ->when(
                $result = $database->query(
                    'SELECT * FROM users',
                    $database::FETCH_CLASS,
                    'StdClass'
                )
            )
            ->then
                ->array($collection = iterator_to_array($result))
                    ->hasSize(1)

                ->let($tuple = $collection[0])
                ->string($tuple->username)
                    ->isEqualTo($login)
                ->string($tuple->digesta1)
                ->boolean(User::checkPassword($password, $tuple->digesta1))
                    ->isTrue();
    }

    /**
     * @tags installation configuration database sqlite authentication administration
     */
    public function case_create_administrator_profile_bad_email()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'authentication' => []
                        ]
                    )
                ),
                $database = new Database($this->helper->sqlite())
            )
            ->exception(function() use($configuration, $database) {
                CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    'a',
                    '💩'
                );
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation')
                ->hasMessage('Email is invalid.');
    }

    /**
     * @tags installation configuration database sqlite authentication administration
     */
    public function case_create_administrator_profile_bad_password()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'authentication' => []
                        ]
                    )
                ),
                $database = new Database($this->helper->sqlite())
            )
            ->exception(function() use($configuration, $database) {
                CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    'gordon@freeman.hl',
                    ''
                );
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation')
                ->hasMessage('Password is invalid.');
    }

    public function remove(array &$array, $key1, $key2 = null)
    {
        if (isset($array[$key1])) {

            if (null !== $key2 && isset($array[$key1][$key2])) {
                unset($array[$key1][$key2]);
            }

            unset($array[$key1]);

        }

        return;
    }
}
