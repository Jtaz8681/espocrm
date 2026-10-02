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

namespace Espo\Core\Currency;

use Espo\Core\Utils\Metadata;

/**
 * @since 9.3.0
 */
class PrecisionProvider
{
    private const int DEFAULT_PRECISION = 2;

    public function __construct(
        private Metadata $metadata,
    ) {}

    public function get(?string $code): int
    {
        if (!$code) {
            return self::DEFAULT_PRECISION;
        }

        return $this->metadata->get("app.currency.precisionMap.$code") ?? self::DEFAULT_PRECISION;
    }
}
