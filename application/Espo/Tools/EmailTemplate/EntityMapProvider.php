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

namespace Espo\Tools\EmailTemplate;

use Espo\Core\Acl\GlobalRestriction;
use Espo\Core\AclManager;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @since 9.2.0
 * @internal
 */
class EntityMapProvider
{
    public function __construct(
        private EntityManager $entityManager,
        private AclManager $aclManager,
        private ServiceContainer $serviceContainer,
        private Metadata $metadata,
    ) {}

    /**
     * @return array<string, Entity>
     */
    public function get(Entity $entity, User $user, bool $applyAcl): array
    {
        /** @var array<string, string> $map */
        $map = $this->metadata->get("app.emailTemplate.entityLinkMapping.{$entity->getEntityType()}") ?? [];

        $output = [];

        foreach ($map as $entityType => $link) {
            $related = $this->getRelated(
                entity: $entity,
                link: $link,
                user: $user,
                applyAcl: $applyAcl,
            );

            if ($related) {
                $output[$entityType] = $related;
            }
        }

        return $output;
    }

    private function getRelated(
        Entity $entity,
        string $link,
        User $user,
        bool $applyAcl,
    ): ?Entity {

        $entityDefs = $this->entityManager->getDefs()->getEntity($entity->getEntityType());

        $forbiddenLinkList = $this->aclManager->getScopeRestrictedLinkList(
            $entity->getEntityType(),
            [
                GlobalRestriction::TYPE_FORBIDDEN,
                GlobalRestriction::TYPE_INTERNAL,
                GlobalRestriction::TYPE_ONLY_ADMIN,
            ]
        );

        if ($applyAcl) {
            if (
                $entityDefs->hasField($link) &&
                !$this->aclManager->checkField($user, $entity->getEntityType(), $link)
            ) {
                return null;
            }

            if (in_array($link, $forbiddenLinkList)) {
                return null;
            }
        }

        $related = $this->entityManager
            ->getRelation($entity, $link)
            ->findOne();

        if (!$related) {
            return null;
        }

        if (
            $applyAcl &&
            !$this->aclManager->checkEntityRead($user, $related)
        ) {
            return null;
        }

        $this->serviceContainer
            ->get($related->getEntityType())
            ->loadAdditionalFields($related);

        return $related;
    }
}
