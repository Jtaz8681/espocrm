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

namespace Espo\Classes\JobPreparators;

use Espo\Core\Job\Preparator;
use Espo\Core\Job\Preparator\Data;
use Espo\Core\Name\Field;
use Espo\ORM\EntityManager;
use Espo\Entities\EmailAccount;
use Espo\Core\Job\Preparator\CollectionHelper;

use DateTimeImmutable;

class CheckEmailAccounts implements Preparator
{
    /**
     * @param CollectionHelper<EmailAccount> $helper
     */
    public function __construct(
        private EntityManager $entityManager,
        private CollectionHelper $helper
    ) {}

    public function prepare(Data $data, DateTimeImmutable $executeTime): void
    {
        $collection = $this->entityManager
            ->getRDBRepositoryByClass(EmailAccount::class)
            ->join(Field::ASSIGNED_USER, 'assignedUserAdditional')
            ->where([
                'status' => EmailAccount::STATUS_ACTIVE,
                'useImap' => true,
                'assignedUserAdditional.isActive' => true,
            ])
            ->find();

        $this->helper->prepare($collection, $data, $executeTime);
    }
}
