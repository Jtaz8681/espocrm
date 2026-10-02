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

namespace Espo\Core\Formula\Functions\RecordGroup;

use Espo\Core\Acl\SystemRestriction;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\ORM\Type\FieldType;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use stdClass;

class FetchType implements Func
{
    public function __construct(
        private EntityManager $entityManager,
        private SystemRestriction $systemRestriction,
    ) {}

    public function process(EvaluatedArgumentList $arguments): ?stdClass
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(1);
        }

        $entityType = $arguments[0] ?? null;
        $id = $arguments[1] ?? null;

        if (!is_string($entityType)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(2, 'string');
        }

        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            return null;
        }

        $this->load($entity);

        foreach ($this->systemRestriction->getReadRestrictedAttributeList($entityType) as $attribute) {
            $entity->clear($attribute);
        }

        return $entity->getValueMap();
    }

    private function load(Entity $entity): void
    {
        if (!$entity instanceof CoreEntity) {
            return;
        }

        $fieldDefsList = $this->entityManager
            ->getDefs()
            ->getEntity($entity->getEntityType())
            ->getFieldList();

        foreach ($fieldDefsList as $fieldDefs) {
            $field = $fieldDefs->getName();

            if ($fieldDefs->getType() === FieldType::LINK_MULTIPLE && $entity->hasLinkMultipleField($field)) {
                $entity->loadLinkMultipleField($field);
            }
        }
    }
}
