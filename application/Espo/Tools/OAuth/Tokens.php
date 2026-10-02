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

namespace Espo\Tools\OAuth;

use Espo\Core\Field\DateTime;
use SensitiveParameter;

/**
 * Immutable.
 */
class Tokens
{
    public function __construct(
        #[SensitiveParameter]
        private string $accessToken,
        #[SensitiveParameter]
        private ?string $refreshToken,
        private ?DateTime $expiresAt,
    ) {}

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt;
    }
}
