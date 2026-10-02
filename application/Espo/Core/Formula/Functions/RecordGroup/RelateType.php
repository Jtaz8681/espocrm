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
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Di;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class RelateType extends BaseFunction implements
    Di\EntityManagerAware,
    Di\InjectableFactoryAware
{
    use Di\EntityManagerSetter;
    use Di\InjectableFactorySetter;

    public function process(ArgumentList $args)
    {
        if (count($args) < 4) {
            $this->throwTooFewArguments(4);
        }

        $entityType = $this->evaluate($args[0]);
        $id = $this->evaluate($args[1]);
        $link = $this->evaluate($args[2]);
        $foreignId = $this->evaluate($args[3]);
        $columnData = count($args) > 4 ? $this->evaluate($args[4]) : null;

        if (!$entityType || !is_string($entityType)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(2, 'string');
        }

        if (!$link || !is_string($link)) {
            throw BadArgumentType::create(3, 'string');
        }

        if ($columnData !== null && !$columnData instanceof stdClass) {
            throw BadArgumentType::create(4, 'object');
        }

        $this->assertLinkWrite($entityType, $link);

        if ($columnData instanceof stdClass) {
            $columnData = get_object_vars($columnData);
        }

        if (!$foreignId) {
            return null;
        }

        $em = $this->entityManager;

        if (!$em->hasRepository($entityType)) {
            $this->throwError("Repository does not exist.");
        }

        $entity = $em->getEntityById($entityType, $id);

        if (!$entity) {
            return null;
        }

        $relation = $em->getRDBRepository($entityType)->getRelation($entity, $link);

        if (is_array($foreignId)) {
            foreach ($foreignId as $itemId) {
                $relation->relateById($itemId, $columnData);
            }

            return true;
        }

        if (!is_string($foreignId)) {
            $this->throwError("foreignId type is wrong.");
        }

        $relation->relateById($foreignId, $columnData);

        return true;
    }

    /**
     * @throws NotAllowedUsage
     */
    private function assertLinkWrite(string $entityType, string $link): void
    {
        $restriction = $this->injectableFactory->create(SystemRestriction::class);

        if (!$restriction->checkEntityTypeWrite($entityType)) {
            throw new NotAllowedUsage("Cannot write '$entityType'.");
        }

        if (!$restriction->checkLinkWrite($entityType, $link) ) {
            throw new NotAllowedUsage("Cannot write restricted link $entityType.$link.");
        }
    }
}
