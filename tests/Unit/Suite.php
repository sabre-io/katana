<?php

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Test\Helper;
use atoum;

/**
 * Unit test suite parent class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Suite extends atoum\test
{
    public function __construct()
    {
        $self = $this;

        $this->setMethodPrefix('case');
        parent::__construct();

        // Avoid conflict with \Mock.
        $this->getMockGenerator()->setDefaultNamespace('Mouck');

        $assertionManager = $this->getAssertionManager();

        // Register helpers.
        $helpers = new Helper\Helper();
        $helpers->registerHelper('configuration', new Helper\Configuration());
        $helpers->registerHelper('sqlite',        new Helper\SQLite());
        $assertionManager->setHandler(
            'helper',
            function() use($helpers) {
                return $helpers;
            }
        );

        // let.
        $assertionManager->setMethodHandler(
            'let',
            function() use($self) {
                return $self;
            }
        );

        return;
    }

    public function getTestedClassName()
    {
        return 'StdClass';
    }

    public function getTestedClassNamespace()
    {
        return '\\';
    }

    public function beforeTestMethod($methodName)
    {
        $out             = parent::beforeTestMethod($methodName);
        $testedClassName = self::getTestedClassNameFromTestClass(
            $this->getClass(),
            $this->getTestNamespace()
        );
        $testedNamespace = substr(
            $testedClassName,
            0,
            strrpos($testedClassName, '\\')
        );

        $this->getPhpMocker()->setDefaultNamespace($testedNamespace);

        return $out;
    }
}
