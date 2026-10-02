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

namespace Espo\Tools\EmailFolder;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\GroupEmailFolder;
use Espo\ORM\EntityManager;

class GroupFolderService
{
    private EntityManager $entityManager;
    private Acl $acl;

    public function __construct(EntityManager $entityManager, Acl $acl)
    {
        $this->entityManager = $entityManager;
        $this->acl = $acl;
    }

    /**
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function moveUp(string $id): void
    {
        /** @var ?GroupEmailFolder $entity */
        $entity = $this->entityManager->getEntityById(GroupEmailFolder::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        $currentIndex = $entity->getOrder();

        if (!is_int($currentIndex)) {
            throw new Error();
        }

        $previousEntity = $this->entityManager
            ->getRDBRepositoryByClass(GroupEmailFolder::class)
            ->where([
                'order<' => $currentIndex,
            ])
            ->order('order', true)
            ->findOne();

        if (!$previousEntity) {
            return;
        }

        $entity->set('order', $previousEntity->getOrder());

        $previousEntity->set('order', $currentIndex);

        $this->entityManager->saveEntity($entity);
        $this->entityManager->saveEntity($previousEntity);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     * @throws Error
     */
    public function moveDown(string $id): void
    {
        /** @var ?GroupEmailFolder $entity */
        $entity = $this->entityManager->getEntityById(GroupEmailFolder::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }
        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        $currentIndex = $entity->getOrder();

        if (!is_int($currentIndex)) {
            throw new Error();
        }

        $nextEntity = $this->entityManager
            ->getRDBRepositoryByClass(GroupEmailFolder::class)
            ->where([
                'order>' => $currentIndex,
            ])
            ->order('order', false)
            ->findOne();

        if (!$nextEntity) {
            return;
        }

        $entity->set('order', $nextEntity->getOrder());

        $nextEntity->set('order', $currentIndex);

        $this->entityManager->saveEntity($entity);
        $this->entityManager->saveEntity($nextEntity);
    }
}
