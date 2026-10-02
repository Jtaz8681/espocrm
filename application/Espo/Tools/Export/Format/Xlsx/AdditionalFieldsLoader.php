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

namespace Espo\Tools\Export\Format\Xlsx;

use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Entity;
use Espo\Tools\Export\AdditionalFieldsLoader as AdditionalFieldsLoaderInterface;

/**
 * @noinspection PhpUnused
 */
class AdditionalFieldsLoader implements AdditionalFieldsLoaderInterface
{
    public function __construct(private Metadata $metadata)
    {}

    public function load(Entity $entity, array $fieldList): void
    {
        if (!$entity instanceof CoreEntity) {
            return;
        }

        foreach ($entity->getRelationList() as $link) {
            if (!in_array($link, $fieldList)) {
                continue;
            }

            if ($entity->getRelationType($link) === Entity::BELONGS_TO_PARENT) {
                if (!$entity->get($link . 'Name')) {
                    $entity->loadParentNameField($link);
                }
            } else if (
                (
                    (
                        $entity->getRelationType($link) === Entity::BELONGS_TO &&
                        $entity->getRelationParam($link, RelationParam::NO_JOIN)
                    ) ||
                    $entity->getRelationType($link) === Entity::HAS_ONE
                ) &&
                $entity->hasAttribute($link . 'Name')
            ) {
                if (!$entity->get($link . 'Name') || !$entity->get($link . 'Id')) {
                    $entity->loadLinkField($link);
                }
            }
        }

        foreach ($fieldList as $field) {
            $fieldType = $this->metadata
                ->get(['entityDefs', $entity->getEntityType(), 'fields', $field, 'type']);

            if ($fieldType === FieldType::LINK_MULTIPLE || $fieldType === FieldType::ATTACHMENT_MULTIPLE) {
                if (!$entity->has($field . 'Ids') && $entity->hasLinkMultipleField($field)) {
                    $entity->loadLinkMultipleField($field);
                }
            }
        }
    }
}
