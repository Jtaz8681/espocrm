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

namespace Espo\Core\Mail\Account\PersonalAccount;

use Espo\Core\Exceptions\Error;
use Espo\Core\InjectableFactory;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Entities\EmailAccount;
use Espo\ORM\EntityManager;

class AccountFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private EntityManager $entityManager
    ) {}

    /**
     * @throws Error
     */
    public function create(string $id): Account
    {
        $entity = $this->entityManager->getEntityById(EmailAccount::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new Error("EmailAccount '{$id}' not found.");
        }

        $binding = BindingContainerBuilder::create()
            ->bindInstance(EmailAccount::class, $entity)
            ->build();

        return $this->injectableFactory->createWithBinding(Account::class, $binding);
    }
}
