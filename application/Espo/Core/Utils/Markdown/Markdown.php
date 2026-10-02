<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

namespace Espo\Core\Utils\Markdown;

use Michelf\MarkdownExtra as MarkdownParser;

/**
 * @internal
 */
class Markdown
{
    /**
     * @internal
     */
    public static function transform(string $text): string
    {
        $parser = new MarkdownParser();
        $parser->no_markup = true;
        $parser->no_entities = true;

        return $parser->transform($text);
    }
}
