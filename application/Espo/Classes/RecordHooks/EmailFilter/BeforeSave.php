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

namespace Espo\Classes\RecordHooks\EmailFilter;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\EmailAccount as EmailAccountEntity;
use Espo\Entities\EmailFilter;
use Espo\Entities\InboundEmail as InboundEmailEntity;
use Espo\Entities\User as UserEntity;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<EmailFilter>
 */
class BeforeSave implements SaveHook
{
    public function __construct(
        private Acl $acl
    ) {}

    /**
     * @inheritDoc
     */
    public function process(Entity $entity): void
    {
        // Check if own.
        if ($entity->isNew() && !$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        $this->controlEntityValues($entity);
    }

    /**
     * @throws Forbidden
     */
    private function controlEntityValues(EmailFilter $entity): void
    {
        if ($entity->isGlobal()) {
            $entity->setMultiple([
                'parentType' => null,
                'parentId' => null,
            ]);

            if ($entity->getAction() !== EmailFilter::ACTION_SKIP) {
                throw new Forbidden("Not allowed `action`.");
            }
        }

        if ($entity->getParentType() && !$entity->getParentId()) {
            throw new Forbidden("Not allowed `parentId` value.");
        }

        if (
            $entity->getParentType() === UserEntity::ENTITY_TYPE &&
            !in_array(
                $entity->getAction(),
                [
                    EmailFilter::ACTION_NONE,
                    EmailFilter::ACTION_SKIP,
                    EmailFilter::ACTION_MOVE_TO_FOLDER,
                ]
            )
        ) {
            throw new Forbidden("Not allowed `action`.");
        }

        if (
            $entity->getParentType() === InboundEmailEntity::ENTITY_TYPE &&
            !in_array(
                $entity->getAction(),
                [
                    EmailFilter::ACTION_SKIP,
                    EmailFilter::ACTION_MOVE_TO_GROUP_FOLDER,
                ]
            )
        ) {
            throw new Forbidden("Not allowed `action`.");
        }

        if (
            $entity->getParentType() === EmailAccountEntity::ENTITY_TYPE &&
            $entity->getAction() !== EmailFilter::ACTION_SKIP
        ) {
            throw new Forbidden("Not allowed `action`.");
        }

        if ($entity->getAction() !== EmailFilter::ACTION_MOVE_TO_FOLDER) {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            $entity->set('emailFolderId', null);
        }

        if ($entity->getAction() !== EmailFilter::ACTION_MOVE_TO_GROUP_FOLDER) {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            $entity->set('groupEmailFolderId', null);
        }
    }
}
