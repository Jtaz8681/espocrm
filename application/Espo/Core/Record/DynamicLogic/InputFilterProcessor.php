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

namespace Espo\Core\Record\DynamicLogic;

use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\Tools\DynamicLogic\ConditionChecker;
use Espo\Tools\DynamicLogic\ConditionCheckerFactory;
use Espo\Tools\DynamicLogic\Exceptions\BadCondition;
use Espo\Tools\DynamicLogic\Item;
use RuntimeException;
use stdClass;

class InputFilterProcessor
{
    public function __construct(
        private Metadata $metadata,
        private FieldUtil $fieldUtil,
        private ConditionCheckerFactory $conditionCheckerFactory,
    ) {}

    public function process(Entity $entity, stdClass $input): void
    {
        /** @var array<string, array<string, mixed>> $fieldsDefs */
        $fieldsDefs = $this->metadata->get("logicDefs.{$entity->getEntityType()}.fields") ?? [];

        $checker = null;

        foreach ($fieldsDefs as $field => $defs) {
            if ($defs['readOnlySaved'] ?? null) {
                $checker ??= $this->conditionCheckerFactory->create($entity);

                $this->processField($entity, $input, $field, $checker);
            }
        }
    }

    private function processField(Entity $entity, stdClass $input, string $field, ConditionChecker $checker): void
    {
        /** @var ?stdClass[] $group */
        $group = $this->metadata
            ->getObjects("logicDefs.{$entity->getEntityType()}.fields.$field.readOnlySaved.conditionGroup");

        if (!$group) {
            return;
        }

        try {
            $item = Item::fromGroupDefinition($group);

            if (!$checker->check($item)) {
                return;
            }
        } catch (BadCondition $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        foreach ($this->fieldUtil->getAttributeList($entity->getEntityType(), $field) as $attribute) {
            unset($input->$attribute);
        }
    }
}
