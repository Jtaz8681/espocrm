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

namespace Espo\Core\Authentication\Jwt;

use Espo\Core\Authentication\Jwt\Exceptions\UnsupportedKey;
use Espo\Core\Authentication\Jwt\Keys\Rsa;
use stdClass;

class DefaultKeyFactory implements KeyFactory
{
    private const TYPE_RSA = 'RSA';

    public function create(stdClass $raw): Key
    {
        $kty = $raw->kty ?? null;

        if ($kty === self::TYPE_RSA) {
            return Rsa::fromRaw($raw);
        }

        throw new UnsupportedKey();
    }
}
