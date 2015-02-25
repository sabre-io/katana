<?php

namespace Sabre\Katana\Test\Unit;

use Sabre\Katana\Database as LUT;

/**
 * Test suite of the database component.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Database extends Suite
{
    public function case_extends_pdo()
    {
        $this
            ->given($sqlite = $this->helper->sqlite())
            ->when($result = new LUT($sqlite))
            ->then
                ->object($result)
                    ->isInstanceOf('PDO');
    }

    public function case_template_schema_iterator()
    {
        $this
            ->given(
                $sqlite   = $this->helper->sqlite(),
                $database = new LUT($sqlite)
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
