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

namespace Espo\Core\Utils\Theme;

use Espo\Core\Utils\Metadata;

/**
 * @internal
 * @since 9.1.0
 */
class MetadataProvider
{
    private string $defaultStylesheet = 'client/css/espo/espo.css';

    public function __construct(
        private Metadata $metadata,
    ) {}

    public function getStylesheet(string $theme): string
    {
        return $this->metadata->get("themes.$theme.stylesheet") ?? $this->defaultStylesheet;
    }

    public function isDark(string $theme): bool
    {
        return (bool) $this->metadata->get("themes.$theme.isDark");
    }
}
