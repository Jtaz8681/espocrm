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

namespace Espo\Modules\Crm\Tools\TargetList;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\HookManager;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use RuntimeException;

class RecordService
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
        private HookManager $hookManager,
        private MetadataProvider $metadataProvider
    ) {}

    /**
     * Unlink all targets.
     *
     * @throws Forbidden
     * @throws NotFound
     * @throws BadRequest
     */
    public function unlinkAll(string $id, string $link): void
    {
        $entity = $this->getEntity($id);

        $linkEntityType = $this->getLinkEntityType($entity, $link);

        $updateQuery = $this->entityManager->getQueryBuilder()
            ->update()
            ->in($linkEntityType)
            ->set([Attribute::DELETED => true])
            ->where(['targetListId' => $entity->getId()])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($updateQuery);

        $this->hookManager->process(TargetList::ENTITY_TYPE, 'afterUnlinkAll', $entity, [], ['link' => $link]);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function getEntity(string $id): TargetList
    {
        $entity = $this->entityManager->getRDBRepositoryByClass(TargetList::class)->getById($id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->check($entity, Table::ACTION_EDIT)) {
            throw new Forbidden();
        }

        return $entity;
    }

    /**
     * @throws BadRequest
     */
    private function getLinkEntityType(TargetList $entity, string $link): string
    {
        if (!in_array($link, $this->metadataProvider->getTargetLinkList())) {
            throw new BadRequest("Not supported link.");
        }

        $foreignEntityType = $entity->getRelationParam($link, RelationParam::ENTITY);

        if (!$foreignEntityType) {
            throw new RuntimeException();
        }

        $linkEntityType = ucfirst($entity->getRelationParam($link, RelationParam::RELATION_NAME) ?? '');

        if ($linkEntityType === '') {
            throw new RuntimeException();
        }

        return $linkEntityType;
    }
}
