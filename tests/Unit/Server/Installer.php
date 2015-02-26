<?php

namespace Sabre\Katana\Test\Unit\Server;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Server\Installer as CUT;
use Sabre\Katana\Configuration;
use Sabre\Katana\Database;
use Sabre\HTTP;

/**
 * Test suite of the installer.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Installer extends Suite
{
    protected $_defaultConfiguration = [
        'baseUrl'          => '/',
        'database' => [
            'type'     => 'sqlite',
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

    public function case_redirect_to_index()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'server.json',
                        ['base_uri' => '/mybase/']
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

    public function case_redirect_to_install()
    {
        $this
            ->given(
                $request  = new HTTP\Request(),
                $request->setBaseUrl('/mybase/'),
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

    public function case_check_correct_login()
    {
        $this
            ->given($login = '💩')
            ->when($result = CUT::checkLogin($login))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_check_incorrect_login()
    {
        $this
            ->given($login = '')
            ->when($result = CUT::checkLogin($login))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

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

    public function case_create_configuration_file()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type']     = 'sqlite',
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
                ->array($content['authentification'])
                    ->hasKey('realm')
                ->array($content['database'])
                    ->hasKey('dsn')
                    ->hasKey('username')
                    ->hasKey('password')

                ->string($content['base_url'])
                    ->isEqualTo('/')
                ->string($content['authentification']['realm'])
                    ->matches('#^[a-f0-9]{40}$#')
                ->string($content['database']['dsn'])
                    ->matches('#^sqlite:katana://data/variable/database/katana_\d+\.sqlite#')
                ->string($content['database']['username'])
                    ->isEqualTo('foo')
                ->string($content['database']['password'])
                    ->isEqualTo('bar');
    }

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

    public function case_create_configuration_file_database_type_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $this->remove($content, 'database', 'type')
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_type_is_empty()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = ''
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

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

    public function case_create_configuration_file_database_mysql_host_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'mysql',
                $content['database']['port'] = '42',
                $content['database']['name'] = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_mysql_empty_host()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'mysql',
                $content['database']['host'] = '',
                $content['database']['port'] = '42',
                $content['database']['name'] = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_mysql_port_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'mysql',
                $content['database']['host'] = 'foo',
                $content['database']['name'] = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_mysql_empty_port()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'mysql',
                $content['database']['host'] = 'foo',
                $content['database']['port'] = '',
                $content['database']['name'] = 'bar'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_mysql_name_is_required()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'mysql',
                $content['database']['host'] = 'foo',
                $content['database']['port'] = '42'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_mysql_empty_name()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'mysql',
                $content['database']['host'] = 'foo',
                $content['database']['port'] = '42',
                $content['database']['name'] = ''
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_unknown_type()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'crazydb'
            )
            ->exception(function() use($filename, $content) {
                CUT::createConfigurationFile($filename, $content);
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation');
    }

    public function case_create_configuration_file_database_sqlite_dsn()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'sqlite'
            )
            ->when($result = CUT::createConfigurationFile($filename, $content))
            ->then
                ->string($result->database->dsn)
                    ->matches('#^sqlite:katana://data/variable/database/katana_\d+\.sqlite#');
    }

    public function case_create_configuration_file_database_mysql_dsn()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'mysql',
                $content['database']['host'] = 'foo',
                $content['database']['port'] = '42',
                $content['database']['name'] = 'bar'
            )
            ->when($result = CUT::createConfigurationFile($filename, $content))
            ->then
                ->string($result->database->dsn)
                    ->isEqualTo('mysql:host=foo;port=42;dbname=bar');
    }

    public function case_create_database()
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

    public function case_create_administrator_profile()
    {
        $this
            ->given(
                $realm         = '🔒',
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'authentification' => [
                                'realm' => $realm
                            ],
                            'database' => [
                                'dsn'      => $this->helper->sqlite(),
                                'username' => '',
                                'password' => ''
                            ]
                        ]
                    )
                ),
                $database = CUT::createDatabase($configuration),
                $login    = 'gordon',
                $email    = 'gordon@freeman.hl',
                $password = '💩'
            )
            ->when(
                $result = CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    $login,
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
                    ->isEqualTo('principals/admin')
                ->string($tuple->email)
                    ->isEqualTo($email)
                ->string($tuple->displayname)
                    ->isEqualTo('Administrator')

                ->let($tuple = $collection[1])
                ->string($tuple->id)
                    ->isEqualTo('2')
                ->string($tuple->uri)
                    ->isEqualTo('principals/admin/calendar-proxy-read')
                ->variable($tuple->email)
                    ->isNull()
                ->variable($tuple->displayname)
                    ->isNull()

                ->let($tuple = $collection[2])
                ->string($tuple->id)
                    ->isEqualTo('3')
                ->string($tuple->uri)
                    ->isEqualTo('principals/admin/calendar-proxy-write')
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
                    ->isEqualTo('gordon')
                ->string($tuple->digesta1)
                    ->isEqualTo(md5($login . ':' . $realm . ':' . $password));
    }

    public function case_create_administrator_profile_authentification_is_required()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration('configuration.json'),
                    true
                ),
                $database = new Database($this->helper->sqlite())
            )
            ->exception(function() use($configuration, $database) {
                CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    null,
                    null,
                    null
                );
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation')
                ->hasMessage('Configuration is corrupted, the authentification branch is missing.');
    }

    public function case_create_administrator_profile_bad_login()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'authentification' => []
                        ]
                    )
                ),
                $database = new Database($this->helper->sqlite())
            )
            ->exception(function() use($configuration, $database) {
                CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    '',
                    'gordon@freeman.hl',
                    '💩'
                );
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation')
                ->hasMessage('Login is invalid.');
    }

    public function case_create_administrator_profile_bad_email()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'authentification' => []
                        ]
                    )
                ),
                $database = new Database($this->helper->sqlite())
            )
            ->exception(function() use($configuration, $database) {
                CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    'gordon',
                    'a',
                    '💩'
                );
            })
                ->isInstanceOf('Sabre\Katana\Exception\Installation')
                ->hasMessage('Email is invalid.');
    }

    public function case_create_administrator_profile_bad_password()
    {
        $this
            ->given(
                $configuration = new Configuration(
                    $this->helper->configuration(
                        'configuration.json',
                        [
                            'authentification' => []
                        ]
                    )
                ),
                $database = new Database($this->helper->sqlite())
            )
            ->exception(function() use($configuration, $database) {
                CUT::createAdministratorProfile(
                    $configuration,
                    $database,
                    'gordon',
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
