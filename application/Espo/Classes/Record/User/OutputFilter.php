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

namespace Espo\Classes\Record\User;

use Espo\Core\Authentication\Logins\Hmac;
use Espo\Core\Record\Output\Filter;
use Espo\Core\Utils\ApiKey;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements Filter<User>
 */
class OutputFilter implements Filter
{
    public function __construct(
        private User $user,
        private ApiKey $apiKey
    ) {}

    public function filter(Entity $entity): void
    {
        $entity->clear('sendAccessInfo');

        $this->filterApiUser($entity);
    }

    private function filterApiUser(User $entity): void
    {
        if (!$entity->isApi()) {
            return;
        }

        if ($this->user->isAdmin()) {
            if ($entity->getAuthMethod() === Hmac::NAME) {
                $secretKey = $this->apiKey->getSecretKeyForUserId($entity->getId());

                $entity->set('secretKey', $secretKey);
            }

            return;
        }

        $entity->clear('apiKey');
        $entity->clear('secretKey');
    }
}
