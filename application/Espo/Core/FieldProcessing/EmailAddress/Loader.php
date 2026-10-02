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

namespace Espo\Core\FieldProcessing\EmailAddress;

use Espo\Repositories\EmailAddress as Repository;
use Espo\ORM\Entity;
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

        if (!$entityDefs->hasField('emailAddress')) {
            return;
        }

        if ($entityDefs->getField('emailAddress')->getType() !== 'email') {
            return;
        }

        /** @var Repository $repository */
        $repository = $this->entityManager->getRepository('EmailAddress');

        $emailAddressData = $repository->getEmailAddressData($entity);

        $entity->set('emailAddressData', $emailAddressData);
        $entity->setFetched('emailAddressData', $emailAddressData);
    }
}
