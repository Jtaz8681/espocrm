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

namespace Espo\Classes\Record\Hooks\PipelineStage;

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Name\Field;
use Espo\Core\Record\DeleteParams;
use Espo\Core\Record\Hook\DeleteHook;
use Espo\Entities\PipelineStage;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Throwable;

/**
 * @implements DeleteHook<PipelineStage>
 */
class BeforeDelete implements DeleteHook
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function process(Entity $entity, DeleteParams $params): void
    {
        try {
            $pipeline = $entity->getPipeline();
        } catch (Throwable) {
            return;
        }

        $entityType = $entity->getPipeline()->getTargetEntityType();

        if (!$this->entityManager->hasRepository($entityType)) {
            return;
        }

        $one = $this->entityManager
            ->getRDBRepository($entityType)
            ->where([Field::PIPELINE_STAGE . 'Id' => $entity->getId()])
            ->findOne();

        if ($one) {
            throw Conflict::createWithBody(
                'cannotRemoveUsed',
                Body::create()->withMessageTranslation('cannotRemoveUsed')
            );
        }
    }
}
