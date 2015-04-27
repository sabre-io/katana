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

namespace Sabre\Katana\Test\CodingStyle;

use Symfony\CS\AbstractFixer;
use Symfony\CS\FixerInterface;
use Symfony\CS\Tokenizer\Tokens;

/**
 * Public visibility is omitted.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class PublicVisibility extends AbstractFixer {

    function getDescription() {
        return 'Public visibility is omitted.';
    }

    function getName() {
        return 'public_visibility';
    }

    function getLevel() {
        return FixerInterface::CONTRIB_LEVEL;
    }

    function fix(\SplFileInfo $file, $content) {
        $tokens = Tokens::fromCode($content);

        foreach ($tokens as $i => $token) {
            if ($token->isGivenKind(T_PUBLIC)) {
                $token->clear();
                $tokens->removeTrailingWhitespace($i);
            }
        }

        return $tokens->generateCode();
    }

    function getPriority() {
        return -42;
    }

}
