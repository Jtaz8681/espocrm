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

namespace Espo\Tools\ActionHistory;

use Espo\Core\Name\Field;
use Espo\Core\Record\ActionHistory\Action;
use Espo\Core\Record\Collection as RecordCollection;
use Espo\Entities\ActionHistoryRecord;
use Espo\Core\FieldProcessing\ListLoadProcessor;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;
use Espo\Entities\User;

class Service
{
    public function __construct(
        private Metadata $metadata,
        private EntityManager $entityManager,
        private User $user,
        private ListLoadProcessor $listLoadProcessor
    ) {}

    /**
     * @return RecordCollection<ActionHistoryRecord>
     */
    public function getLastViewed(?int $maxSize, ?int $offset): RecordCollection
    {
        $scopes = $this->metadata->get('scopes');

        $targetTypeList = array_filter(
            array_keys($scopes),
            function ($item) use ($scopes) {
                return !empty($scopes[$item]['object']) || !empty($scopes[$item]['lastViewed']);
            }
        );

        $maxSize = $maxSize ?? 0;
        $offset = $offset ?? 0;

        $collection = $this->entityManager
            ->getRDBRepositoryByClass(ActionHistoryRecord::class)
            ->where([
                'userId' => $this->user->getId(),
                'action' => [Action::READ, Action::CREATE],
                'targetType' => $targetTypeList,
            ])
            ->order('MAX:' . Field::CREATED_AT, 'DESC')
            ->select([
                'targetId',
                'targetType',
                'MAX:number',
                ['MAX:createdAt', Field::CREATED_AT],
            ])
            ->group(['targetId', 'targetType'])
            ->limit($offset, $maxSize + 1)
            ->find();

        foreach ($collection as $entity) {
            $this->listLoadProcessor->process($entity);

            $entity->set('id', Util::generateId());
        }

        return RecordCollection::createNoCount($collection,  $maxSize);
    }
}
