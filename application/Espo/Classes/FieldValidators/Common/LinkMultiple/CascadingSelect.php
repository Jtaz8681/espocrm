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

namespace Espo\Classes\FieldValidators\Common\LinkMultiple;

use Espo\Core\Field\LinkMultiple;
use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\ORM\Defs;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Tools\DynamicLogic\CascadingFields\ItemsProvider;
use Espo\Tools\DynamicLogic\CascadingFields\ValidationHelper;

/**
 * @implements Validator<Entity>
 */
class CascadingSelect implements Validator
{
    public function __construct(
        private EntityManager $entityManager,
        private Defs $defs,
        private ValidationHelper $helper,
        private ItemsProvider $itemsProvider,
    ) {}

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        if (!$entity instanceof CoreEntity) {
            return null;
        }

        $items = $this->itemsProvider->get($entity->getEntityType(), $field);

        if (!$items) {
            return null;
        }

        $linkValue = $entity->getValueObject($field);

        if (!$linkValue instanceof LinkMultiple || !$linkValue->getIdList()) {
            return null;
        }

        $entityType = $this->defs
            ->getEntity($entity->getEntityType())
            ->tryGetRelation($field)
            ?->tryGetForeignEntityType();

        if (!$entityType) {
            return null;
        }

        $valueEntities = $this->entityManager
            ->getRDBRepository($entityType)
            ->where([
                Attribute::ID => $linkValue->getIdList(),
            ])
            ->find();

        if (!count($valueEntities)) {
            return null;
        }

        foreach ($valueEntities as $valueEntity) {
            if (!$valueEntity instanceof CoreEntity) {
                continue;
            }

            foreach ($items as $item) {
                $itemFailure = $this->helper->validateItem($entity, $valueEntity, $item);

                if ($itemFailure) {
                    return $itemFailure;
                }
            }
        }

        return null;
    }
}
