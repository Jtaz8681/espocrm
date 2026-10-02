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

namespace Espo\Modules\Crm\Hooks\Opportunity;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Repository\Option\SaveContext;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<Opportunity>
 */
class Contacts implements AfterSave
{
    public function __construct(private EntityManager $entityManager) {}

    /**
     * @param Opportunity $entity
     */
    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isAttributeChanged('contactId')) {
            return;
        }

        /** @var ?string $contactId */
        $contactId = $entity->get('contactId');
        $contactIdList = $entity->get('contactsIds') ?? [];
        $fetchedContactId = $entity->getFetched('contactId');

        $relation = $this->entityManager
            ->getRDBRepositoryByClass(Opportunity::class)
            ->getRelation($entity, 'contacts');

        if (!$contactId) {
            if ($fetchedContactId && $relation->isRelatedById($fetchedContactId)) {
                $relation->unrelateById($fetchedContactId, [
                    SaveContext::NAME => $options->get(SaveContext::NAME),
                ]);
            }

            return;
        }

        if (in_array($contactId, $contactIdList)) {
            return;
        }

        $contact = $this->entityManager
            ->getRDBRepositoryByClass(Contact::class)
            ->getById($contactId);

        if (!$contact) {
            return;
        }

        if ($relation->isRelated($contact)) {
            return;
        }

        $relation->relateById($contactId, null, [
            SaveContext::NAME => $options->get(SaveContext::NAME),
        ]);
    }
}
