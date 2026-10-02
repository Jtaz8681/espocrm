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

namespace Espo\Modules\Crm\Tools\Meeting\Invitation;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Entities\User;

/**
 * @since 9.0.0
 */
class SenderFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
    ) {}

    /**
     * Create a sender for a user. The SMTP of the user will be used (if not disabled in the config).
     */
    public function createForUser(User $user): Sender
    {
        return $this->injectableFactory->createWithBinding(
            Sender::class,
            BindingContainerBuilder::create()
                ->bindInstance(User::class, $user)
                ->build()
        );
    }
}
