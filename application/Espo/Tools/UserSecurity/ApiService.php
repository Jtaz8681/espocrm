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

namespace Espo\Tools\UserSecurity;

use Espo\Core\Authentication\Logins\Hmac;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\Util;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

class ApiService
{
    private ServiceContainer $serviceContainer;
    private User $user;
    private EntityManager $entityManager;

    public function __construct(
        ServiceContainer $serviceContainer,
        User $user,
        EntityManager $entityManager
    ) {
        $this->serviceContainer = $serviceContainer;
        $this->user = $user;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function generateNewApiKey(string $id): User
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $service = $this->serviceContainer->get(User::ENTITY_TYPE);

        /** @var ?User $entity */
        $entity = $service->getEntity($id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$entity->isApi()) {
            throw new Forbidden();
        }

        $apiKey = Util::generateApiKey();

        $entity->set('apiKey', $apiKey);

        if ($entity->getAuthMethod() === Hmac::NAME) {
            $secretKey = Util::generateSecretKey();

            $entity->set('secretKey', $secretKey);
        }

        $this->entityManager->saveEntity($entity);

        $service->prepareEntityForOutput($entity);

        return $entity;
    }
}
