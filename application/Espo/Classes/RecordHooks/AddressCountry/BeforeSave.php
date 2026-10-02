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

namespace Espo\Classes\RecordHooks\AddressCountry;

use Espo\Core\Exceptions\ConflictSilent;
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\AddressCountry;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<AddressCountry>
 */
class BeforeSave implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function process(Entity $entity): void
    {
        $where = ['name' => $entity->getName()];

        if (!$entity->isNew()) {
            $where['id!='] = $entity->getId();
        }

        $one = $this->entityManager
            ->getRDBRepositoryByClass(AddressCountry::class)
            ->where($where)
            ->findOne();

        if (!$one) {
            return;
        }

        throw ConflictSilent::createWithBody(
            'duplicateError',
            Body::create()->withMessageTranslation('duplicateConflict')
        );
    }
}
