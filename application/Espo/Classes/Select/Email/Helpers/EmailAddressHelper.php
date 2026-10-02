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

namespace Espo\Classes\Select\Email\Helpers;

use Espo\Entities\EmailAddress;
use Espo\ORM\EntityManager;

class EmailAddressHelper
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEmailAddressIdByValue(string $value): ?string
    {
        $emailAddress = $this->entityManager
            ->getRDBRepository(EmailAddress::ENTITY_TYPE)
            ->where([
                'lower' => strtolower($value),
            ])
            ->findOne();

        if (!$emailAddress) {
            return null;
        }

        return $emailAddress->getId();
    }
}
