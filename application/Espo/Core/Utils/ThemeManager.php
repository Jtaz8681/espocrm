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

namespace Espo\Core\Utils;

use Espo\Core\Utils\Theme\MetadataProvider;

class ThemeManager
{
    private string $defaultName = 'Espo';

    private string $defaultLogoSrc = 'client/img/logo.svg';

    public function __construct(
        private Config $config,
        private Metadata $metadata,
        private MetadataProvider $metadataProvider,
    ) {}

    public function getName(): string
    {
        return $this->config->get('theme') ?? $this->defaultName;
    }

    public function getStylesheet(): string
    {
        return $this->metadataProvider->getStylesheet($this->getName());
    }

    public function getLogoSrc(): string
    {
        return $this->metadata->get(['themes', $this->getName(), 'logo']) ?? $this->defaultLogoSrc;
    }

    public function isDark(): bool
    {
        return $this->metadataProvider->isDark($this->getName());
    }
}
