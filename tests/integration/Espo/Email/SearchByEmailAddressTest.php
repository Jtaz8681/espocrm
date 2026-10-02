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

namespace tests\integration\Espo\Email;

use Espo\Core\Record\ServiceContainer;
use Espo\Core\Select\SearchParams;
use Espo\Entities\Email;
use integration\Core\NoTransaction;
use tests\integration\Core\BaseTestCase;

class SearchByEmailAddressTest extends BaseTestCase
{
    public function testSearchByEmailAddress()
    {
        $entityManager = $this->getEntityManager();

        $email = $entityManager->getNewEntity('Email');

        $email->set('from', 'test@test.com');
        $email->set('status', 'Archived');

        $entityManager->saveEntity($email);

        $emailService = $this->getApplication()
            ->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Email::class);

        $result = $emailService->find(
            SearchParams::fromRaw([
                'where' => [
                    [
                        'type' => 'equals',
                        'attribute' => 'emailAddress',
                        'value' => 'test@test.com'
                    ]
                ]
            ])
        );

        $this->assertEquals(1, count($result->getCollection()));
    }

    /**
     * Full-text search index is not applied for uncommited data.
     */
    #[NoTransaction]
    public function testTextSearch()
    {
        $entityManager = $this->getEntityManager();

        $email = $entityManager->getNewEntity('Email');

        $email->set('from', 'test@test.com');
        $email->set('status', 'Archived');
        $email->set('name', 'Improvements to our Privacy Policy');
        $email->set('body', 'name abc test');

        $entityManager->saveEntity($email);

        $emailService = $this->getApplication()
            ->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Email::class);

        $result = $emailService->find(
            SearchParams::fromRaw([
                'textFilter' => 'name abc'
            ])
        );

        $this->assertEquals(1, count($result->getCollection()));

        $result = $emailService->find(
             SearchParams::fromRaw([
                'textFilter' => 'Improvements to our Privacy Policy'
            ])
        );

        $this->assertEquals(1, count($result->getCollection()));
    }
}
