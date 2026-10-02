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

namespace Espo\Core\Portal\Utils;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Theme\MetadataProvider;
use Espo\Entities\Portal;
use Espo\Core\Utils\ThemeManager as BaseThemeManager;

class ThemeManager extends BaseThemeManager
{
    private Portal $portal;

    public function __construct(
        Config $config,
        Metadata $metadata,
        MetadataProvider $metadataProvider,
        Portal $portal,
    ) {
        parent::__construct($config, $metadata, $metadataProvider);

        $this->portal = $portal;
    }

    public function getName(): string
    {
        $theme = $this->portal->get('theme');

        if ($theme) {
            return $theme;
        }

        return parent::getName();
    }
}
