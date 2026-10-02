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

namespace Espo\Classes\RecordHooks\User;

use Espo\Core\Acl\Cache\Clearer;
use Espo\Core\DataManager;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Entity;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<User>
 * @noinspection PhpUnused
 */
class AfterUpdate implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private Clearer $clearer,
        private DataManager $dataManager
    ) {}

    public function process(Entity $entity): void
    {
        $this->processCache($entity);
        $this->processContactName($entity);
    }

    private function processCache(User $entity): void
    {
        if (
            $entity->isAttributeChanged('rolesIds') ||
            $entity->isAttributeChanged('teamsIds') ||
            $entity->isAttributeChanged('type') ||
            $entity->isAttributeChanged('portalRolesIds') ||
            $entity->isAttributeChanged('portalsIds')
        ) {
            $this->clearer->clearForUser($entity);
            $this->dataManager->updateCacheTimestamp();
        }

        if (
            $entity->isAttributeChanged('portalRolesIds') ||
            $entity->isAttributeChanged('portalsIds') ||
            $entity->isAttributeChanged('contactId') ||
            $entity->isAttributeChanged('accountsIds')
        ) {
            $this->clearer->clearForAllPortalUsers();
            $this->dataManager->updateCacheTimestamp();
        }
    }

    private function processContactName(User $entity): void
    {
        if (
            !$entity->isPortal() ||
            !$entity->getContactId() ||
            !$entity->isAttributeChanged('firstName') &&
            !$entity->isAttributeChanged('lastName') &&
            !$entity->isAttributeChanged('salutationName')
        ) {
            return;
        }

        $contact = $this->entityManager->getEntityById(Contact::ENTITY_TYPE, $entity->getContactId());

        if (!$contact) {
            return;
        }

        if ($entity->isAttributeChanged('firstName')) {
            $contact->set('firstName', $entity->get('firstName'));
        }

        if ($entity->isAttributeChanged('lastName')) {
            $contact->set('lastName', $entity->get('lastName'));
        }

        if ($entity->isAttributeChanged('salutationName')) {
            $contact->set('salutationName', $entity->get('salutationName'));
        }

        $this->entityManager->saveEntity($contact);
    }
}
