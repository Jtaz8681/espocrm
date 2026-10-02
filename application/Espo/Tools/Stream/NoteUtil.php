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

namespace Espo\Tools\Stream;

use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Entities\Note;

/**
 * @internal
 */
class NoteUtil
{
    public function __construct(private ApplicationConfig $applicationConfig) {}

    public function handlePostText(Note $entity): void
    {
        $post = $entity->getPost();

        if (!$post) {
            return;
        }

        $siteUrl = $this->applicationConfig->getSiteUrl();

        // PhpStorm inspection highlights RegExpRedundantEscape by a mistake.
        /** @noinspection RegExpRedundantEscape */
        $regexp = '/(\s|^)' . preg_quote($siteUrl, '/') .
            '(\/portal|\/portal\/[a-zA-Z0-9]*)?\/#([A-Z][a-zA-Z0-9]*)\/view\/([a-zA-Z0-9-]*)/';

        $post = preg_replace($regexp, '\1[\3/\4](#\3/view/\4)', $post);

        $entity->setPost($post);
    }
}
