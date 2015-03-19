<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2015  fruux GmbH (https://fruux.com/)
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

use Sabre\Katana\Database as CUT;

/**
 * Test suite of the database component.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 *
 * @tags database
 */
class Database extends Suite
{
    public function case_extends_pdo()
    {
        $this
            ->given($sqlite = $this->helper->sqlite())
            ->when($result = new CUT($sqlite))
            ->then
                ->object($result)
                    ->isInstanceOf('PDO');
    }

    public function case_template_schema_iterator()
    {
        $this
            ->given(
                $sqlite   = $this->helper->sqlite(),
                $database = new CUT($sqlite)
            )
            ->when($result = $database->getTemplateSchemaIterator())
            ->then
                ->object($result)
                    ->isInstanceOf('Hoa\File\Finder')
                ->foreach(
                    $result,
                    function($test, $value, $key) {
                        $test
                            ->string($key)
                                ->match('/\.sqlite.sql/')
                            ->object($value)
                                ->isInstanceOf('Hoa\File\SplFileInfo')
                            ->string($value->getFilename())
                                ->match('/\.sqlite.sql/');
                    }
                );
    }
}
