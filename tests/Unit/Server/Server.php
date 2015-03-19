<?php

namespace Sabre\Katana\Test\Unit\Server;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Server\Server as CUT;
use Mock;

/**
 * Test suite of the server.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Server extends Suite
{
    /**
     * @tags server authentification
     */
    public function case_unauthorized()
    {
        $this
            ->given($server = new Mock\Server())
            ->when($server->run())
            ->then
                ->integer($server->response->getStatus())
                    ->isEqualTo(401);
    }

    /**
     * @tags server authentification
     */
    public function case_authorized()
    {
        $this
            ->given(
                $server = new Mock\Server(),
                $server->request->addHeader(
                    'Authorization',
                    'Basic ' .
                    base64_encode(
                        $server::ADMINISTRATOR_LOGIN .
                        ':' .
                        $server::ADMINISTRATOR_PASSWORD
                    )
                )
            )
            ->when($server->run())
            ->then
                ->integer($server->response->getStatus())
                    ->isNotEqualTo(401);
    }
}
