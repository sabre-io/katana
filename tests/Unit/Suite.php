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

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Test\Helper;
use atoum;

/**
 * Unit test suite parent class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Suite extends atoum\test
{
    const defaultNamespace = '#\\\Test\\\Unit\\\#';

    function __construct() {
        $self = $this;

        $this->setMethodPrefix('case');
        parent::__construct();

        // Avoid conflict with \Mock.
        $this->getMockGenerator()->setDefaultNamespace('Mouck');

        $assertionManager = $this->getAssertionManager();

        // Register helpers.
        $helpers = new Helper\Helper();
        $helpers->registerHelper('configuration',      new Helper\Configuration());
        $helpers->registerHelper('sqlite',             new Helper\SQLite());
        $helpers->registerHelper('mysql',              new Helper\MySQL());
        $helpers->registerHelper('temporaryFile',      new Helper\TemporaryFile());
        $helpers->registerHelper('temporaryDirectory', new Helper\TemporaryDirectory());
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

    }

    function getTestedClassName() {
        return 'StdClass';
    }

    function getTestedClassNamespace() {
        return '\\';
    }

    function beforeTestMethod($methodName) {
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
