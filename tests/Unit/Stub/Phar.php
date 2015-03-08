<?php

namespace Sabre\Katana\Test\Unit\Stub;

use Sabre\Katana\Test\Unit\Suite;
use Sabre\Katana\Stub\Phar as CUT;

/**
 * Test suite of the PHAR archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 *
 * @tags stub phar
 */
class Phar extends Suite
{
    public function case_signature()
    {
        $this
            ->given(
                $filename = $this->helper->phar(
                    $this->sample(
                        $this->realdom->regex('/\w+\.phar/')
                    )
                ),
                $phar        = new CUT($filename),
                $phar['foo'] = 'bar'
            )
            ->when($result = $phar->getSignature())
            ->then
                ->string($result['hash_type'])
                    ->isEqualTo('SHA-1');
    }

    public function case_metadata()
    {
        $this
            ->given(
                $phar = new CUT(
                    $this->helper->phar(
                        $this->sample(
                            $this->realdom->regex('/\w+\.phar/')
                        )
                    )
                )
            )
            ->when($result = $phar->getMetadata())
            ->then
                ->array($result)
                    ->hasKeys([
                        'author',
                        'license',
                        'copyright',
                        'datetime'
                    ])
                ->string($result['author'])
                    ->isEqualTo('fruux GmbH (https://fruux.com/)')
                ->string($result['license'])
                    ->isEqualTo('Modified BSD License (http://sabre.io/license/)')
                ->string($result['copyright'])
                    ->isEqualTo('Copyright (C) 2015 fruux GmbH (https://fruux.com/)')
                ->string($result['datetime'])
                    ->isEqualTo(date('c', strtotime($result['datetime'])));
    }
}
