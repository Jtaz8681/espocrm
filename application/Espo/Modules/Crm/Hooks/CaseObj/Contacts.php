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

namespace Espo\Modules\Crm\Hooks\CaseObj;

use Espo\Core\FieldProcessing\Stream\FollowersLoader;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\Stream\Service as StreamService;

/**
 * @implements AfterSave<CaseObj>
 */
class Contacts implements AfterSave
{
    private const ATTR_CONTACT_ID = 'contactId';
    private const RELATION_CONTACTS = 'contacts';

    public function __construct(
        private EntityManager $entityManager,
        private FollowersLoader $followersLoader,
        private StreamService $streamService,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isAttributeChanged(self::ATTR_CONTACT_ID)) {
            return;
        }

        $contact = $entity->getContact();

        /** @var ?string $fetchedContactId */
        $fetchedContactId = $entity->getFetched(self::ATTR_CONTACT_ID);

        $contactsRelation = $this->entityManager->getRelation($entity, self::RELATION_CONTACTS);

        if ($fetchedContactId) {
            $previousPortalUser = $this->entityManager
                ->getRDBRepositoryByClass(User::class)
                ->select([Attribute::ID])
                ->where([
                    'contactId' => $fetchedContactId,
                    'type' => User::TYPE_PORTAL,
                ])
                ->findOne();

            if ($previousPortalUser) {
                $this->streamService->unfollowEntity($entity, $previousPortalUser->getId());

                $this->followersLoader->processFollowers($entity);
            }
        }

        if (!$contact && $fetchedContactId) {
            $contactsRelation->unrelateById($fetchedContactId);

            return;
        }

        if (!$contact) {
            return;
        }

        $portalUser = $this->getPortalUser($contact->getId());

        if ($portalUser && !$entity->isInternal()) {
            // @todo Solve ACL check issue when a user is in multiple portals.
            $this->streamService->followEntity($entity, $portalUser->getId());

            if (!$entity->isNew()) {
                $this->followersLoader->processFollowers($entity);
            }
        }

        if (in_array($contact->getId(), $entity->getContacts()->getIdList())) {
            return;
        }

        if ($contactsRelation->isRelatedById($contact->getId())) {
            return;
        }

        $contactsRelation->relateById($contact->getId());
    }

    private function getPortalUser(?string $contactId): ?User
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->select([Attribute::ID])
            ->where([
                'contactId' => $contactId,
                'type' => User::TYPE_PORTAL,
                'isActive' => true,
            ])
            ->findOne();
    }
}
