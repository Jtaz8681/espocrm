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

namespace Espo\Core\Record\ConcurrencyControl;

use Espo\Core\Name\Field;
use Espo\Core\Record\ConcurrencyControl\Optimistic\Result;
use Espo\Core\Utils\FieldUtil;
use Espo\ORM\BaseEntity;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @internal
 */
class OptimisticProcessor
{
    public function __construct(
        private EntityManager $entityManager,
        private FieldUtil $fieldUtil,
    ) {}

    public function process(Entity $entity, int $versionNumber): ?Result
    {
        $previousVersionNumber = $entity->getFetched(Field::VERSION_NUMBER);

        if ($previousVersionNumber === null) {
            return null;
        }

        if ($versionNumber === $previousVersionNumber) {
            return null;
        }

        $changedFieldList = [];

        $entityDefs = $this->entityManager
            ->getDefs()
            ->getEntity($entity->getEntityType());

        foreach ($entityDefs->getFieldList() as $fieldDefs) {
            $field = $fieldDefs->getName();

            if (
                $fieldDefs->getParam('optimisticConcurrencyControlIgnore') ||
                $fieldDefs->getParam(FieldParam::READ_ONLY)
            ) {
                continue;
            }

            foreach ($this->fieldUtil->getActualAttributeList($entityDefs->getName(), $field) as $attribute) {
                if (
                    $entity instanceof BaseEntity &&
                    !$entity->isAttributeWritten($attribute)
                ) {
                    continue;
                }

                if (!$entity->hasFetched($attribute)) {
                    continue;
                }

                if ($entity->isAttributeChanged($attribute)) {
                    $changedFieldList[] = $field;

                    continue 2;
                }
            }
        }

        if ($changedFieldList === []) {
            return null;
        }

        $values = (object) [];

        foreach ($changedFieldList as $field) {
            foreach ($this->fieldUtil->getAttributeList($entityDefs->getName(), $field) as $attribute) {
                $values->$attribute = $entity->getFetched($attribute);
            }
        }

        return new Result(
            fieldList: $changedFieldList,
            values: $values,
            versionNumber: $previousVersionNumber,
        );
    }
}
