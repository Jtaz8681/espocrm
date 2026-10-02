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

namespace Espo\Core\Api;

use Espo\Core\Authentication\Authentication;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Binding\ContextualBinder;
use Espo\Core\InjectableFactory;

use RuntimeException;

/**
 * Builds Auth instance.
 */
class AuthBuilder
{
    private bool $authRequired = false;
    private bool $isEntryPoint = false;
    private ?Authentication $authentication = null;

    public function __construct(private InjectableFactory $injectableFactory)
    {}

    public function setAuthentication(Authentication $authentication): self
    {
        $this->authentication = $authentication;

        return $this;
    }

    public function setAuthRequired(bool $authRequired): self
    {
        $this->authRequired = $authRequired;

        return $this;
    }

    public function forEntryPoint(): self
    {
        $this->isEntryPoint = true;

        return $this;
    }

    public function build(): Auth
    {
        if (!$this->authentication) {
            throw new RuntimeException("Authentication is not set.");
        }

        return $this->injectableFactory->createWithBinding(
            Auth::class,
            BindingContainerBuilder
                ::create()
                ->bindInstance(Authentication::class, $this->authentication)
                ->inContext(Auth::class, function (ContextualBinder $binder) {
                    $binder
                        ->bindValue('$authRequired', $this->authRequired)
                        ->bindValue('$isEntryPoint', $this->isEntryPoint);
                })
                ->build()
        );
    }
}
