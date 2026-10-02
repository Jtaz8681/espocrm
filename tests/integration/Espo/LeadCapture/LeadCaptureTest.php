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

namespace tests\integration\Espo\LeadCapture;

use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\LeadCapture;
use Espo\ORM\EntityManager;
use Espo\Tools\LeadCapture\CaptureService;
use tests\integration\Core\BaseTestCase;

class LeadCaptureTest extends BaseTestCase
{
    public function testCapture():void
    {
        $entityManager = $this->getContainer()->getByClass(EntityManager::class);

        $targetList = $entityManager->getNewEntity('TargetList');
        $entityManager->saveEntity($targetList);

        $team = $entityManager->getNewEntity('Team');
        $entityManager->saveEntity($team);

        $recordService = $this->getContainer()->getByClass(ServiceContainer::class)->getByClass(LeadCapture::class);
        $service = $this->getInjectableFactory()->create(CaptureService::class);

        $leadCaptureData = (object) [
            'name' => 'test',
            'subscribeToTargetList' => true,
            'targetListId' => $targetList->getId(),
            'targetTeamId' => $team->getId(),
            'fieldList' => ['name', 'emailAddress'],
            'leadSource' => 'Web Site'
        ];

        $leadCapture = $recordService->create($leadCaptureData, CreateParams::create());

        $this->assertNotEmpty($leadCapture->get('apiKey'));

        $data = (object) [
            'firstName' => 'Test',
            'lastName' => 'Tester',
            'emailAddress' => 'test@tester.com'
        ];

        $service->capture($leadCapture->get('apiKey'), $data);

        $lead = $entityManager
            ->getRDBRepository('Lead')
            ->where(['emailAddress' => 'test@tester.com'])
            ->findOne();

        $this->assertNotNull($lead);

        $this->assertEquals('Web Site', $lead->get('source'));
        $this->assertTrue($entityManager->getRelation($lead, 'teams')->isRelatedById($team->getId()));
        $this->assertTrue($entityManager->getRelation($lead, 'targetLists')->isRelatedById($targetList->getId()));
    }
}
