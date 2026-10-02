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

namespace Espo\Core\FieldProcessing\PhoneNumber;

use Espo\Core\ORM\Type\FieldType;
use Espo\Entities\PhoneNumber;
use Espo\ORM\Entity;
use Espo\Repositories\PhoneNumber as Repository;
use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;

use Espo\ORM\Defs as OrmDefs;

/**
 * @implements LoaderInterface<Entity>
 */
class Loader implements LoaderInterface
{
    public function __construct(private OrmDefs $ormDefs, private EntityManager $entityManager)
    {}

    public function process(Entity $entity, Params $params): void
    {
        $entityDefs = $this->ormDefs->getEntity($entity->getEntityType());

        if (!$entityDefs->hasField('phoneNumber')) {
            return;
        }

        if ($entityDefs->getField('phoneNumber')->getType() !== FieldType::PHONE) {
            return;
        }

        /** @var Repository $repository */
        $repository = $this->entityManager->getRepository(PhoneNumber::ENTITY_TYPE);

        $phoneNumberData = $repository->getPhoneNumberData($entity);

        $entity->set('phoneNumberData', $phoneNumberData);
        $entity->setFetched('phoneNumberData', $phoneNumberData);
    }
}
