<?php

namespace Sabre\Katana\Stub;

use Phar as PHPPhar;

/**
 * Create a PHAR archive.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Phar extends PHPPhar
{
    /**
     * Open a PHAR archive. If it does not exist, attempt to create it.
     *
     * @param  string  $filename    Filename (see original documentation).
     * @param  int     $flags       Flags (see original documentation).
     * @param  string  $alias       Alias (see original documentation).
     * @return void
     */
    public function __construct($filename, $flags = null, $alias = null)
    {
        if(null !== $alias) {
            parent::__construct($filename, $flags, $alias);
        } elseif (null !== $flags) {
            parent::__construct($filename, $flags);
        } else {
            parent::__construct($filename);
        }

        $this->setSignatureAlgorithm(static::SHA1);
        $this->setMetadata([
            'author'    => 'fruux GmbH (https://fruux.com/)',
            'license'   => 'Modified BSD License (http://sabre.io/license/)',
            'copyright' => 'Copyright (C) 2015 fruux GmbH (https://fruux.com/)',
            'datetime'  => date('c')
        ]);

        return;
    }
}
