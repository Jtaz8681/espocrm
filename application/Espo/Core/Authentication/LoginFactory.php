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

namespace Espo\Core\Authentication;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

class LoginFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private ConfigDataProvider $configDataProvider
    ) {}

    public function create(string $method, bool $isPortal = false): Login
    {
        /** @var class-string<Login> $className */
        $className = $this->metadata->get(['authenticationMethods', $method, 'implementationClassName']);

        if (!$className) {
            $sanitizedName = preg_replace('/[^a-zA-Z0-9]+/', '', $method);

            /** @var class-string<Login> $className */
            $className = "Espo\\Core\\Authentication\\Logins\\" . $sanitizedName;
        }

        return $this->injectableFactory->createWith($className, [
            'isPortal' => $isPortal,
        ]);
    }

    public function createDefault(): Login
    {
        $method = $this->configDataProvider->getDefaultAuthenticationMethod();

        return $this->create($method);
    }
}
