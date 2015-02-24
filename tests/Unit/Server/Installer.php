<?php

namespace Sabre\Katana\Test\Unit\Server;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Server\Installer as LUT;
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
    public function case_is_installed()
    {
        $this
            ->given($this->function->file_exists = true)
            ->when($result = LUT::isInstalled())
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_is_not_installed()
    {
        $this
            ->given($this->function->file_exists = false)
            ->when($result = LUT::isInstalled())
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
            ->when($result = LUT::redirectToIndex($response, $configuration))
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
            ->when($result = LUT::redirectToInstall($response, $request))
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
                        ->boolean($result = LUT::checkBaseUrl($baseUrl))
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
                        ->boolean($result = LUT::checkBaseUrl($baseUrl))
                            ->isFalse();
                }
            });
    }

    public function case_check_correct_password()
    {
        $this
            ->given($_password = $this->realdom->regex('#[\w\d_!\-@💩]+#'))
            ->when(function() use($_password) {
                foreach ($this->realdom->sampleMany($_password, 100) as $password) {
                    $this
                        ->given($passwords = $password . $password)
                        ->boolean($result = LUT::checkPassword($passwords))
                            ->isTrue();
                }
            });
    }

    public function case_check_incorrect_empty_password()
    {
        $this
            ->given($password = '')
            ->when($result = LUT::checkPassword($password . $password))
            ->then
                ->boolean($result)
                    ->isFalse()

            ->given($password = null)
            ->when($result = LUT::checkPassword($password . $password))
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
                        ->given($result = LUT::checkPassword($password . $confirmed))
                        ->boolean($result)
                            ->isFalse();
                }
            });
    }
}
