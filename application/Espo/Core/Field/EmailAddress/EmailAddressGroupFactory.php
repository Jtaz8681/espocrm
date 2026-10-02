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

namespace Espo\Core\Field\EmailAddress;

use Espo\Core\ORM\Type\FieldType;
use Espo\Entities\EmailAddress as EmailAddressEntity;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\Repositories\EmailAddress as Repository;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Value\ValueFactory;

use Espo\Core\Field\EmailAddress;
use Espo\Core\Field\EmailAddressGroup;
use Espo\Core\Utils\Metadata;

use RuntimeException;
use stdClass;

/**
 * An email address group factory.
 */
class EmailAddressGroupFactory implements ValueFactory
{
    private Metadata $metadata;
    private EntityManager $entityManager;

    /**
     * @todo Use OrmDefs instead of Metadata.
     */
    public function __construct(Metadata $metadata, EntityManager $entityManager)
    {
        $this->metadata = $metadata;
        $this->entityManager = $entityManager;
    }

    public function isCreatableFromEntity(Entity $entity, string $field): bool
    {
        $type = $this->metadata->get(['entityDefs', $entity->getEntityType(), 'fields', $field, FieldParam::TYPE]);

        if ($type !== FieldType::EMAIL) {
            return false;
        }

        return true;
    }

    public function createFromEntity(Entity $entity, string $field): EmailAddressGroup
    {
        if (!$this->isCreatableFromEntity($entity, $field)) {
            throw new RuntimeException();
        }

        $emailAddressList = [];

        $primaryEmailAddress = null;

        $dataList = null;

        $dataAttribute = $field . 'Data';

        if ($entity->has($dataAttribute)) {
            $dataList = $this->sanitizeDataList(
                $entity->get($dataAttribute)
            );
        }

        if (!$dataList && $entity->has($field) && !$entity->get($field)) {
            $dataList = [];
        }

        if (!$dataList) {
            /** @var Repository $repository */
            $repository = $this->entityManager->getRepository(EmailAddressEntity::ENTITY_TYPE);

            $dataList = $repository->getEmailAddressData($entity);
        }

        foreach ($dataList as $item) {
            $emailAddress = EmailAddress::create($item->emailAddress);

            if ($item->optOut ?? false) {
                $emailAddress = $emailAddress->optedOut();
            }

            if ($item->invalid ?? false) {
                $emailAddress = $emailAddress->invalid();
            }

            if ($item->primary ?? false) {
                $primaryEmailAddress = $emailAddress;
            }

            $emailAddressList[] = $emailAddress;
        }

        $group = EmailAddressGroup::create($emailAddressList);

        if ($primaryEmailAddress) {
            $group = $group->withPrimary($primaryEmailAddress);
        }

        return $group;
    }

    /**
     * @param array<int, array<string, mixed>|stdClass> $dataList
     * @return stdClass[]
     */
    private function sanitizeDataList(array $dataList): array
    {
        $sanitizedDataList = [];

        foreach ($dataList as $item) {
            if (is_array($item)) {
                $sanitizedDataList[] = (object) $item;

                continue;
            }

            if (!is_object($item)) {
                throw new RuntimeException("Bad data.");
            }

            $sanitizedDataList[] = $item;
        }

        return $sanitizedDataList;
    }
}
