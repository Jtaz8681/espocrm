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

namespace Espo\Controllers;

use Espo\Tools\App\LanguageService as Service;

use Espo\Core\Api\Request;

class I18n
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @return array<string, mixed>
     */
    public function getActionRead(Request $request): array
    {
        $default = $request->getQueryParam('default') === 'true';

        return $this->service->getDataForFrontend($default);
    }
}
