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

namespace tests\integration\Espo\Core\Utils\ScheduledJob;

use Espo\Core\Utils\ScheduledJob\Populator;
use Espo\Entities\ScheduledJob;
use Espo\ORM\Query\DeleteBuilder;
use tests\integration\Core\BaseTestCase;

class PopulatorTest extends BaseTestCase
{
    public function testPopulate(): void
    {
        $deleteQuery = DeleteBuilder::create()
            ->from(ScheduledJob::ENTITY_TYPE)
            ->where([
                ScheduledJob::FIELD_JOB => 'Cleanup',
            ])
            ->build();

        $this->getEntityManager()->getQueryExecutor()->execute($deleteQuery);


        $this->assertEquals(0, $this->getJobCount());

        $populator = $this->getInjectableFactory()->create(Populator::class);

        $populator->populate();

        $this->assertEquals(1, $this->getJobCount());
    }

    private function getJobCount(): int
    {
        return $this->getEntityManager()
            ->getRDBRepositoryByClass(ScheduledJob::class)
            ->where([
                ScheduledJob::FIELD_JOB => 'Cleanup',
            ])
            ->count();
    }
}
