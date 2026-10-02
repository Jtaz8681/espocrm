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

namespace Espo\Tools\Stars;

use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DateTime;
use Espo\Core\Utils\Metadata;
use Espo\Entities\StarSubscription;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use PDOException;

class StarService
{
    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata,
        private Config $config
    ) {}

    public function isStarred(Entity $entity, User $user): bool
    {
        return (bool) $this->entityManager
            ->getRDBRepository(StarSubscription::ENTITY_TYPE)
            ->select([Attribute::ID])
            ->where([
                'userId' => $user->getId(),
                'entityType' => $entity->getEntityType(),
                'entityId' => $entity->getId(),
            ])
            ->findOne();
    }

    public function isEnabled(string $entityType): bool
    {
        return (bool) $this->metadata->get("scopes.$entityType.stars");
    }

    /**
     * @throws Forbidden
     */
    public function star(Entity $entity, User $user): void
    {
        if (!$this->isEnabled($entity->getEntityType())) {
            throw new Forbidden();
        }

        if ($this->isStarred($entity, $user)) {
            return;
        }

        $this->checkLimit($entity->getEntityType(), $user);

        try {
            $this->entityManager->createEntity(StarSubscription::ENTITY_TYPE, [
                'entityId' => $entity->getId(),
                'entityType' => $entity->getEntityType(),
                'userId' => $user->getId(),
                Field::CREATED_AT => DateTime::getSystemNowString(),
            ]);
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                // Duplicate.
                return;
            }

            throw $e;
        }
    }

    /**
     * @throws Forbidden
     */
    public function unstar(Entity $entity, User $user): void
    {
        if (!$this->isEnabled($entity->getEntityType())) {
            throw new Forbidden();
        }

        $delete = $this->entityManager->getQueryBuilder()
            ->delete()
            ->from(StarSubscription::ENTITY_TYPE)
            ->where([
                'userId' => $user->getId(),
                'entityId' => $entity->getId(),
                'entityType' => $entity->getEntityType(),
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($delete);
    }

    /**
     * @throws Forbidden
     */
    private function checkLimit(string $entityType, User $user): void
    {
        $limit = $this->config->get('starsLimit');

        if ($limit === null) {
            return;
        }

        $count = $this->entityManager
            ->getRDBRepositoryByClass(StarSubscription::class)
            ->where([
                'userId' => $user->getId(),
                'entityType' => $entityType,
            ])
            ->count();

        if ($count >= $limit) {
            throw Forbidden::createWithBody(
                'starsLimitExceeded',
                Body::create()->withMessageTranslation('starsLimitExceeded')
            );
        }
    }
}
