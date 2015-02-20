<?php

namespace Sabre\Katana\Server;

use Sabre\Katana\Configuration;
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
}
