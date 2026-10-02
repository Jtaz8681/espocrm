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

namespace Espo\Classes\Record\OAuthProvider;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\Input\Data;
use Espo\Core\Record\Input\Filter;
use Espo\Core\Utils\Crypt;

/**
 * @noinspection PhpUnused
 */
class GeneralFilter implements Filter
{
    private const ATTR_CLIENT_SECRET = 'clientSecret';

    public function __construct(private Crypt $crypt) {}

    /**
     * @throws BadRequest
     */
    public function filter(Data $data): void
    {
        $this->processClientSecret($data);
    }

    /**
     * @throws BadRequest
     */
    private function processClientSecret(Data $data): void
    {
        $value = $data->get(self::ATTR_CLIENT_SECRET);

        if ($value === null) {
            return;
        }

        if (!is_string($value)) {
            throw new BadRequest();
        }

        $data->set(self::ATTR_CLIENT_SECRET, $this->crypt->encrypt($value));
    }
}
