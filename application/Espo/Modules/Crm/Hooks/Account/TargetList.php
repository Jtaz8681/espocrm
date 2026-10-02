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

namespace Espo\Modules\Crm\Hooks\Account;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Modules\Crm\Entities\Account;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<Account>
 */
class TargetList implements AfterSave
{
    public function __construct(private EntityManager $entityManager) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$options->get(SaveOption::IMPORT)) {
            return;
        }

        $targetListId = $entity->get('targetListId');

        if (!$targetListId) {
            return;
        }

        $this->entityManager
            ->getRDBRepositoryByClass(Account::class)
            ->getRelation($entity, 'targetLists')
            ->relateById($targetListId);
    }
}
