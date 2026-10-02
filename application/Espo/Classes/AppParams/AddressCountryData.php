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

namespace Espo\Classes\AppParams;

use Espo\Core\Utils\Address\CountryDataProvider;
use Espo\Tools\App\AppParam;

class AddressCountryData implements AppParam
{
    public function __construct(
        private CountryDataProvider $provider
    ) {}

    /**
     * @return array{list: string[], preferredList: string[]}
     */
    public function get(): array
    {
        return $this->provider->get();
    }
}
