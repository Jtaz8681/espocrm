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

use SensitiveParameter;

/**
 * Hash a string. E.g. hash an email address to use it in opt-out URL
 * to recognize a recipient who clicked opt-out.
 */
class Hasher
{
    private string $secretKeyParam = 'hashSecretKey';

    public function __construct(private Config $config)
    {}

    public function hash(#[SensitiveParameter] string $string): string
    {
        $secretKey = $this->config->get($this->secretKeyParam) ?? '';

        return md5(hash_hmac('sha256', $string, $secretKey, true));
    }
}
