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

namespace Espo\Classes\RecordHooks\Event;

use Espo\Core\Record\Hook\UpdateHook;
use Espo\Core\Record\UpdateParams;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Field\DateTime;
use Espo\Core\Field\Date;

use Espo\ORM\Entity;
use Espo\ORM\Defs as OrmDefs;

/**
 * @implements UpdateHook<CoreEntity>
 */
class BeforeUpdatePreserveDuration implements UpdateHook
{
    private OrmDefs $ormDefs;

    public function __construct(OrmDefs $ormDefs)
    {
        $this->ormDefs = $ormDefs;
    }

    public function process(Entity $entity, UpdateParams $params): void
    {
        /** @var CoreEntity $entity */

        if (!$entity->isAttributeChanged('dateStart') && !$entity->isAttributeChanged('dateStartDate')) {
            return;
        }

        if ($entity->isAttributeWritten('dateEnd') || $entity->isAttributeWritten('dateEndDate')) {
            return;
        }

        $preserveDurationDisabled = $this->ormDefs
            ->getEntity($entity->getEntityType())
            ->getField('dateEnd')
            ->getParam('preserveDurationDisabled');

        if ($preserveDurationDisabled) {
            return;
        }

        $this->processDateTime($entity);
        $this->processDate($entity);
    }

    private function processDateTime(Entity $entity): void
    {
        $dateStartFetchedString = $entity->getFetched('dateStart');
        $dateStartString = $entity->get('dateStart');
        $dateEndString = $entity->get('dateEnd');

        if (!$dateStartFetchedString || !$dateStartString || !$dateEndString) {
            return;
        }

        $dateStartFetched = DateTime::fromString($dateStartFetchedString);
        $dateStart = DateTime::fromString($dateStartString);
        $dateEnd = DateTime::fromString($dateEndString);

        $diff = $dateStartFetched->diff($dateEnd);

        $dateEndModified = $dateStart->add($diff);

        $entity->set('dateEnd', $dateEndModified->toString());
    }

    private function processDate(Entity $entity): void
    {
        $dateStartFetchedString = $entity->getFetched('dateStartDate');
        $dateStartString = $entity->get('dateStartDate');
        $dateEndString = $entity->get('dateEndDate');

        if (!$dateStartFetchedString || !$dateStartString || !$dateEndString) {
            return;
        }

        $dateStartFetched = Date::fromString($dateStartFetchedString);
        $dateStart = Date::fromString($dateStartString);
        $dateEnd = Date::fromString($dateEndString);

        $diff = $dateStartFetched->diff($dateEnd);

        $dateEndModified = $dateStart->add($diff);

        $entity->set('dateEndDate', $dateEndModified->toString());
    }
}
