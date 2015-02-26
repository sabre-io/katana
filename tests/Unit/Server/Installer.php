<?php

namespace Sabre\Katana\Test\Unit\Server;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Server\Installer as CUT;
use Sabre\Katana\Configuration;
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
        'baseUrl'  => '/',
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

    public function case_create_configuration_file()
    {
        $this
            ->given(
                $filename = $this->helper->configuration('configuration.json'),
                $content  = $this->_defaultConfiguration,
                $content['database']['type'] = 'sqlite'
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
                    ->isEqualTo('')
                ->string($content['database']['password'])
                    ->isEqualTo('');
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
