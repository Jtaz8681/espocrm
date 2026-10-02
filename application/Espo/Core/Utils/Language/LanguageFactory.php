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

namespace Espo\Core\Utils\Language;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Language;

class LanguageFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Config\SystemConfig $systemConfig,
    ) {}

    public function create(string $language): Language
    {
        return $this->injectableFactory->createWith(Language::class, [
            'language' => $language,
            'useCache' => $this->systemConfig->useCache(),
        ]);
    }
}
