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

namespace Espo\Modules\Crm\Classes\RecordHooks\TargetList;

use Espo\Core\Acl;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\Hook\CreateHook;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\Modules\Crm\Tools\TargetList\MetadataProvider;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements CreateHook<TargetList>
 */
class AfterCreateDuplicate implements CreateHook
{
    public function __construct(
        private Acl $acl,
        private EntityManager $entityManager,
        private MetadataProvider $metadataProvider
    ) {}

    public function process(Entity $entity, CreateParams $params): void
    {
        $id = $params->getDuplicateSourceId();

        if (!$id) {
            return;
        }

        $sourceEntity = $this->entityManager->getRDBRepositoryByClass(TargetList::class)->getById($id);

        if (!$sourceEntity) {
            return;
        }

        if (!$this->acl->check($sourceEntity, Acl\Table::ACTION_READ)) {
            return;
        }

        $this->duplicateLinks($entity, $sourceEntity);
    }

    private function duplicateLinks(TargetList $entity, TargetList $sourceEntity): void
    {
        $repository = $this->entityManager->getRDBRepositoryByClass(TargetList::class);

        foreach ($this->metadataProvider->getTargetLinkList() as $link) {
            $collection = $repository
                ->getRelation($sourceEntity, $link)
                ->where(['@relation.optedOut' => false])
                ->find();

            foreach ($collection as $relatedEntity) {
                $repository
                    ->getRelation($entity, $link)
                    ->relate($relatedEntity, ['optedOut' => false]);
            }
        }
    }
}
