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

use Espo\Core\Container;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\DeleteBuilder;

class BeforeUpgrade
{
    public function run(Container $container)
    {
        $this->deleteDeletedUsers($container->getByClass(EntityManager::class));
    }

    private function deleteDeletedUsers(EntityManager $entityManager): void
    {
        $query = DeleteBuilder::create()
            ->from(User::ENTITY_TYPE)
            ->where(['deleted' => true])
            ->build();

        $entityManager->getQueryExecutor()->execute($query);
    }
}
