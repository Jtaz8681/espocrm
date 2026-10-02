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

namespace Espo\Modules\Crm\Classes\FieldProcessing\Call;

use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;

use Espo\ORM\Name\Attribute;
use stdClass;

/**
 * @implements Loader<Call>
 */
class PhoneNumberMapLoader implements Loader
{
    private const ERASED_PART = 'ERASED:';

    public function __construct(private EntityManager $entityManager)
    {}

    public function process(Entity $entity, Params $params): void
    {
        $map = (object) [];

        assert($entity instanceof CoreEntity);

        $contactIdList = $entity->getLinkMultipleIdList(Meeting::LINK_CONTACTS);

        if (count($contactIdList)) {
            $this->populate($map, Contact::ENTITY_TYPE, $contactIdList);
        }

        $leadIdList = $entity->getLinkMultipleIdList(Meeting::LINK_LEADS);

        if (count($leadIdList)) {
            $this->populate($map, Lead::ENTITY_TYPE, $leadIdList);
        }

        $entity->set('phoneNumbersMap', $map);
    }

    /**
     * @param string[] $idList
     */
    private function populate(stdClass $map, string $entityType, array $idList): void
    {
        $entityList = $this->entityManager
            ->getRDBRepository($entityType)
            ->where([
                Attribute::ID => $idList,
            ])
            ->select([Attribute::ID, 'phoneNumber'])
            ->find();

        foreach ($entityList as $entity) {
            $phoneNumber = $entity->get('phoneNumber');

            if (!$phoneNumber) {
                continue;
            }

            if (str_starts_with($phoneNumber, self::ERASED_PART)) {
                continue;
            }

            $key = $entity->getEntityType() . '_' . $entity->getId();

            $map->$key = $phoneNumber;
        }
    }
}
