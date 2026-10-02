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

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Formula\Utils\EntityUtil;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class UpdateType implements Func
{
    public function __construct(
        private EntityManager $entityManager,
        private EntityUtil $entityUtil,
    ) {}

    public function process(EvaluatedArgumentList $arguments): bool
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(2);
        }

        $entityType = $arguments[0];
        $id = $arguments[1];

        if (!is_string($entityType)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(2, 'string');
        }

        $data = $this->getData($arguments, $entityType);

        $notAllowedAttributes = array_intersect(
            array_keys($data),
            $this->entityUtil->getWriteRestrictedAttributeList($entityType),
        );

        $notAllowedAttributes = array_values($notAllowedAttributes);

        if ($notAllowedAttributes) {
            throw new NotAllowedUsage("Cannot write $entityType.$notAllowedAttributes[0].");
        }

        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            return false;
        }

        $entity->setMultiple($data);

        $this->entityUtil->assertUpdateAccess($entity);

        $this->entityManager->saveEntity($entity);

        return true;
    }

    /**
     * @return array<string, mixed>
     * @throws BadArgumentType
     */
    private function getData(EvaluatedArgumentList $args, mixed $entityType): array
    {
        if (count($args) >= 3 && $args[2] instanceof stdClass) {
            return $this->filterData(get_object_vars($args[2]));
        }

        $data = [];

        $i = 2;

        while ($i < count($args) - 1) {
            $attribute = $args[$i];

            if (!is_string($entityType)) {
                throw BadArgumentType::create($i + 1, 'string');
            }

            /** @var string $attribute */

            $value = $args[$i + 1];

            $data[$attribute] = $value;

            $i = $i + 2;
        }

        return $this->filterData($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterData(array $data): array
    {
        unset($data[Attribute::ID]);

        return $data;
    }
}
