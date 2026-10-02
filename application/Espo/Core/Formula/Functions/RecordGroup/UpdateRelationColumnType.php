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

class UpdateRelationColumnType extends BaseFunction implements
    Di\EntityManagerAware,
    Di\InjectableFactoryAware
{
    use Di\EntityManagerSetter;
    use Di\InjectableFactorySetter;

    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 6) {
            $this->throwTooFewArguments(6);
        }

        $entityType = $args[0];
        $id = $args[1];
        $link = $args[2];
        $foreignId = $args[3];
        $column = $args[4];
        $value = $args[5];

        if (!is_string($entityType)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(2, 'string');
        }

        if (!is_string($link)) {
            throw BadArgumentType::create(3, 'string');
        }

        if (!is_string($foreignId)) {
            throw BadArgumentType::create(4, 'string');
        }

        if (!is_string($column)) {
            throw BadArgumentType::create(5, 'string');
        }

        $this->assertLinkWrite($entityType, $link);

        if (!$column) {
            $this->throwError("Empty column.");
        }

        $em = $this->entityManager;

        if (!$em->hasRepository($entityType)) {
            $this->throwError("Repository does not exist.");
        }

        $entity = $em->getEntityById($entityType, $id);

        if (!$entity) {
            return null;
        }

        $em->getRDBRepository($entityType)
            ->getRelation($entity, $link)
            ->updateColumnsById($foreignId, [$column => $value]);

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
