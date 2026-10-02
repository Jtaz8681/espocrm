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

namespace Espo\Modules\Crm\Classes\FieldProcessing\TargetList;

use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Entity;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\Core\Utils\Metadata;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;

/**
 * @implements Loader<TargetList>
 */
class OptedOutCountLoader implements Loader
{
    /** @var string[] */
    private array $targetLinkList;

    public function __construct(private EntityManager $entityManager, private Metadata $metadata)
    {

        $this->targetLinkList = $this->metadata->get(['scopes', 'TargetList', 'targetLinkList']) ?? [];
    }

    public function process(Entity $entity, Params $params): void
    {
        if (
            $params->hasSelect() &&
            !in_array('optedOutCount', $params->getSelect() ?? [])
        ) {
            return;
        }

        assert($entity instanceof TargetList);

        $count = 0;

        foreach ($this->targetLinkList as $link) {
            $foreignEntityType = $entity->getRelationParam($link, RelationParam::ENTITY);

            $count += $this->entityManager
                ->getRDBRepository($foreignEntityType)
                ->join('targetLists')
                ->where([
                    'targetListsMiddle.targetListId' => $entity->getId(),
                    'targetListsMiddle.optedOut' => true,
                ])
                ->count();
        }

        $entity->set('optedOutCount', $count);
    }
}
