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

use Symfony\CS;

$out      = CS\Config\Config::create();
$iterator = new RegexIterator(
    new FilesystemIterator(__DIR__ . '/CodingStyle'),
    '/\.php$/'
);

foreach ($iterator as $file) {
    require $file->getPathname();

    $classname =
        'Sabre\Katana\Test\CodingStyle\\' .
        substr($file->getFilename(), 0, -4);
    $out->addCustomFixer(new $classname());
}

return
    $out
        ->level(CS\FixerInterface::PSR1_LEVEL)
        ->fixers([
            'align_double_arrow',
            'align_equals',
            'blankline_after_open_tag',
            'concat_with_spaces',
            'self_accessor',
            'short_array_syntax',
            'spaces_cast',
            'unused_use',

            'elseif',
            'eol_ending',
            'function_call_space',
            'function_declaration',
            'indentation',
            'line_after_namespace',
            'linefeed',
            'lowercase_constants',
            'lowercase_keywords',
            'method_argument_space',
            'parenthesis',
            'php_closing_tag',
            'single_line_after_imports',
            'trailing_spaces',
            'visibility',

            // sabre defined
            'public_visibility'
        ]);
