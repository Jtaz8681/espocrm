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

namespace Espo\Modules\Crm\Hooks\Meeting;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<Entity>
 */
class ParentLink implements BeforeSave
{
    public function __construct(private EntityManager $entityManager) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew() && $entity->isAttributeChanged('parentId')) {
            $entity->set('accountId', null);
        }

        if (!$entity->isAttributeChanged('parentId') && !$entity->isAttributeChanged('parentType')) {
            return;
        }

        $parent = null;

        $parentId = $entity->get('parentId');
        $parentType = $entity->get('parentType');

        if ($parentId && $parentType && $this->entityManager->hasRepository($parentType)) {
            $columnList = ['id', 'name'];

            $defs = $this->entityManager->getMetadata()->getDefs();

            if ($defs->getEntity($parentType)->hasAttribute('accountId')) {
                $columnList[] = 'accountId';

            }

            if ($parentType === Lead::ENTITY_TYPE) {
                $columnList[] = 'status';
                $columnList[] = 'createdAccountId';
                $columnList[] = 'createdAccountName';
            }

            $parent = $this->entityManager
                ->getRDBRepository($parentType)
                ->select($columnList)
                ->where([Attribute::ID => $parentId])
                ->findOne();
        }

        $accountId = null;
        $accountName = null;

        if ($parent) {
            if ($parent instanceof Account) {
                $accountId = $parent->getId();
                $accountName = $parent->get(Field::NAME);
            } else if (
                $parent instanceof Lead &&
                $parent->getStatus() === Lead::STATUS_CONVERTED &&
                $parent->get('createdAccountId')
            ) {
                $accountId = $parent->get('createdAccountId');
                $accountName = $parent->get('createdAccountName');
            }

            if (
                !$accountId && $parent->get('accountId') &&
                $parent instanceof CoreEntity &&
                $parent->getRelationParam('account', RelationParam::ENTITY) === Account::ENTITY_TYPE
            ) {
                $accountId = $parent->get('accountId');
            }

            if ($accountId) {
                $entity->set('accountId', $accountId);
                $entity->set('accountName', $accountName);
            }
        }

        if (
            $entity->get('accountId') &&
            !$entity->get('accountName')
        ) {
            $account = $this->entityManager
                ->getRDBRepository(Account::ENTITY_TYPE)
                ->select([Attribute::ID, Field::NAME])
                ->where([Attribute::ID => $entity->get('accountId')])
                ->findOne();

            if ($account) {
                $entity->set('accountName', $account->get(Field::NAME));
            }
        }
    }
}
