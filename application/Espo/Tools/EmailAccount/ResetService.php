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

declare(strict_types=1);

namespace Espo\Tools\EmailAccount;

use Espo\Core\Field\Date;
use Espo\Core\Mail\Account\FetchData;
use Espo\Entities\EmailAccount;
use Espo\Entities\InboundEmail;
use Espo\ORM\EntityManager;

class ResetService
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function reset(InboundEmail|EmailAccount $entity, Date $fetchSince): void
    {
        $number = $entity->getFetchValidityNumber();

        $entity
            ->setFetchSince($fetchSince)
            ->setFetchData(new FetchData())
            ->setFetchValidityNumber($number + 1);

        $this->entityManager->saveEntity($entity);
    }
}
