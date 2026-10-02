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

use Espo\Core\Authentication\Jwt\Exceptions\Expired;
use Espo\Core\Authentication\Jwt\Exceptions\NotBefore;

class Validator
{
    private const DEFAULT_TIME_LEEWAY = 60 * 4;
    private int $timeLeeway;
    private ?int $now;

    public function __construct(
        ?int $timeLeeway = null,
        ?int $now = null
    ) {
        $this->timeLeeway = $timeLeeway ?? self::DEFAULT_TIME_LEEWAY;
        $this->now = $now;
    }

    /**
     * @throws Expired
     * @throws NotBefore
     */
    public function validate(Token $token): void
    {
        $exp = $token->getPayload()->getExp();
        $nbf = $token->getPayload()->getNbf();

        $now = $this->now ?? time();

        if ($exp && $exp + $this->timeLeeway <= $now) {
            throw new Expired("JWT expired.");
        }

        if ($nbf && $now < $nbf - $this->timeLeeway) {
            throw new NotBefore("JWT used before allowed time.");
        }
    }
}
