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

namespace Espo\Tools\FieldManager\Hooks;

use Espo\Core\Di;
use Espo\Entities\NextNumber;

class NumberType implements Di\EntityManagerAware
{
    use Di\EntityManagerSetter;

    /**
     * @param array<string, mixed> $defs
     * @param array<string, mixed> $options
     */
    public function onRead(string $scope, string $name, &$defs, $options): void
    {
        $number = $this->entityManager
            ->getRDBRepository(NextNumber::ENTITY_TYPE)
            ->where([
                'entityType' => $scope,
                'fieldName' => $name,
            ])
            ->findOne();

        $value = null;

        if (!$number) {
            $value = 1;
        } else {
            if (!$number->get('value')) {
                $value = 1;
            }
        }

        if (!$value && $number) {
            $value = $number->get('value');
        }

        $defs['nextNumber'] = $value;
    }

    /**
     * @param array<string, mixed> $defs
     * @param array<string, mixed> $options
     */
    public function afterSave(string $scope, string $name, $defs, $options): void
    {
        if (!isset($defs['nextNumber'])) {
            return;
        }

        $number = $this->entityManager
            ->getRDBRepository(NextNumber::ENTITY_TYPE)
            ->where([
                'entityType' => $scope,
                'fieldName' => $name
            ])
            ->findOne();

        if (!$number) {
            $number = $this->entityManager->getNewEntity(NextNumber::ENTITY_TYPE);

            $number->set('entityType', $scope);
            $number->set('fieldName', $name);
        }

        $number->set('value', $defs['nextNumber']);

        $this->entityManager->saveEntity($number);
    }

    /**
     * @param array<string, mixed> $defs
     * @param array<string, mixed> $options
     */
    public function afterRemove(string $scope, string $name, $defs, $options): void
    {
        $number = $this->entityManager
            ->getRDBRepository(NextNumber::ENTITY_TYPE)
            ->where([
                'entityType' => $scope,
                'fieldName' => $name
            ])
            ->findOne();

        if (!$number) {
            return;
        }

        $this->entityManager->removeEntity($number);
    }
}
