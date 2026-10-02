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

namespace Espo\Classes\Record\Webhook;

use Espo\Core\Record\Defaults\DefaultPopulator;
use Espo\Core\Record\Defaults\Populator;
use Espo\Entities\User;
use Espo\Entities\Webhook;
use Espo\ORM\Entity;

/**
 * @implements Populator<Webhook>
 */
class DefaultsPopulator implements Populator
{
    public function __construct(
        private DefaultPopulator $defaultsDefaultsPopulator,
        private User $user
    ) {}

    public function populate(Entity $entity): void
    {
        $this->defaultsDefaultsPopulator->populate($entity);

        if ($this->user->isApi()) {
            $entity->set('userId', $this->user->getId());
        }
    }
}
