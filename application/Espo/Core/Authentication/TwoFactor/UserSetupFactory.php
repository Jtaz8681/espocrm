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

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

use RuntimeException;

class UserSetupFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata
    ) {}

    public function create(string $method): UserSetup
    {
        /** @var ?class-string<UserSetup> $className */
        $className = $this->metadata->get(['app', 'authentication2FAMethods', $method, 'userSetupClassName']);

        if (!$className) {
            throw new RuntimeException("No user-setup class for '$method'.");
        }

        return $this->injectableFactory->create($className);
    }
}
