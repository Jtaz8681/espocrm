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

namespace Espo\Core\Authentication\TwoFactor;

use Espo\Core\Authentication\TwoFactor\Exceptions\NotConfigured;
use Espo\Core\Exceptions\BadRequest;
use Espo\Entities\User;

use stdClass;

/**
 * 2FA setting-up for a user.
 */
interface UserSetup
{
    /**
     * Get data needed for configuration for a user. Data will be passed to the front-end.
     *
     * @throws NotConfigured
     */
    public function getData(User $user): stdClass;

    /**
     * Verify input data before making 2FA enabled for a user.
     *
     * @throws BadRequest
     */
    public function verifyData(User $user, stdClass $payloadData): bool;
}
