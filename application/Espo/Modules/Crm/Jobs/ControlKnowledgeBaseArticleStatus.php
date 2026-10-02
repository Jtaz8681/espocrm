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

namespace Espo\Modules\Crm\Jobs;

use Espo\Core\Utils\DateTime;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\KnowledgeBaseArticle;
use Espo\Core\Job\JobDataLess;
use Espo\Core\ORM\EntityManager;

class ControlKnowledgeBaseArticleStatus implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata
    ) {}

    public function run(): void
    {
        $statusList = $this->metadata->get("entityDefs.KnowledgeBaseArticle.fields.status.activeOptions") ??
            [KnowledgeBaseArticle::STATUS_PUBLISHED];

        $list = $this->entityManager
            ->getRDBRepository(KnowledgeBaseArticle::ENTITY_TYPE)
            ->where([
                'expirationDate<=' => date(DateTime::SYSTEM_DATE_FORMAT),
                'status' => $statusList,
            ])
            ->find();

        foreach ($list as $e) {
            $e->set('status', KnowledgeBaseArticle::STATUS_ARCHIVED);

            $this->entityManager->saveEntity($e);
        }
    }
}
