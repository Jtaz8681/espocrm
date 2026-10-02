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

namespace tests\unit\Espo\Core\Mail;

use Espo\Entities\Email;
use Espo\Entities\EmailFilter;
use Espo\Core\Mail\FiltersMatcher;
use Espo\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class FiltersMatcherTest extends TestCase
{
    private $object;
    private $entityManager;
    private $emailDefs;
    private $filterDefs;

    protected function setUp() : void
    {
        $this->object = new FiltersMatcher();
        $this->entityManager = $this->createMock(EntityManager::class);

        $this->emailDefs = [
            'attributes' => [
                'from' => [
                    'type' => 'varchar'
                ],
                'to' => [
                    'type' => 'varchar'
                ],
                'name' => [
                    'type' => 'varchar'
                ],
                'subject' => [
                    'type' => 'varchar'
                ],
                'body' => [
                    'type' => 'text'
                ],
                'bodyPlain' => [
                    'type' => 'text'
                ]
            ]
        ];

        $this->filterDefs = [
            'attributes' => [
                'from' => [
                    'type' => 'varchar'
                ],
                'to' => [
                    'type' => 'varchar'
                ],
                'subject' => [
                    'type' => 'varchar'
                ],
                'bodyContains' => [
                    'type' => 'jsonArray'
                ],
                'bodyContainsAll' => [
                    'type' => 'jsonArray'
                ],
            ]
        ];
    }

    protected function tearDown() : void
    {
        $this->object = NULL;
    }

    protected function createEntity(string $entityType, string $className, array $defs)
    {
        return new $className($entityType, $defs, $this->entityManager);
    }

    public function testMatch(): void
    {
        $email = $this->createEntity('Email', Email::class, $this->emailDefs);
        $email->set('from', 'test@tester');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);

        $filter->set([
            'from' => 'test@tester'
        ]);

        $filterList = [$filter];

        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email = $this->createEntity('Email', Email::class, $this->emailDefs);
        $email->set('from', 'test@tester');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);

        $filter->set([
            'from' => '*@tester'
        ]);

        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email->set('from', 'test@tester');
        $email->set('to', 'test@tester;baraka@tester');

        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);

        $filter->set([
            'to' => 'baraka@tester'
        ]);

        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email->set('from', 'test@tester');
        $email->set('to', 'test@tester;baraka@man');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'to' => '*@tester'
        ]);
        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email->set('subject', 'test hello man');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);

        $filter->set([
            'subject' => '*hello*'
        ]);

        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email->set('name', 'test hello man');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'subject' => 'hello'
        ]);
        $filterList = [$filter];
        $this->assertNull($this->object->findMatch($email, $filterList));


        $email->set('name', 'test hello man');
        $email->set('from', 'test@tester');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'subject' => '*hello*',
            'from' => 'test@tester'
        ]);
        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email->set('name', 'test hello man');
        $email->set('from', 'hello@tester');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'subject' => '*hello*',
            'from' => 'test@tester'
        ]);
        $filterList = [$filter];
        $this->assertNull($this->object->findMatch($email, $filterList));


        $email->set('name', 'test hello man');
        $email->set('body', 'one hello three');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'subject' => 'test hello man',
            'bodyContains' => ['hello']
        ]);
        $filterList = [$filter];
        $this->assertNull($this->object->findMatch($email, $filterList, true));

        $email->set('name', 'test hello man');
        $email->set('body', 'one hello three');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'subject' => 'test hello man',
            'bodyContains' => ['hello']
        ]);
        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email->set('name', 'Access information to the BugZyro cloud');
        $email->set('from', 'no-reply@test.com');
        $email->set('to', 'info@test.com');

        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);

        $filter->set([
            'subject' => 'Access information to the BugZyro cloud',
            'from' => 'no-reply@test.com',
            'to' => 'info@test.com'
        ]);

        $this->assertTrue($this->object->match($email, $filter));

        $email->set('body', 'test hello');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'bodyContainsAll' => ['test', 'hello'],
        ]);
        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email->set('body', 'test');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'bodyContainsAll' => ['test', 'hello'],
        ]);
        $filterList = [$filter];
        $this->assertNull($this->object->findMatch($email, $filterList));

        $email->set('body', 'test hello one');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'bodyContains' => ['test', 'one'],
            'bodyContainsAll' => ['test', 'hello'],
        ]);
        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));
    }

    public function testMatchBody()
    {
        $email = $this->createEntity('Email', Email::class, $this->emailDefs);
        $email->set('body', 'hello Man tester');
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'bodyContains' => ['man', 'red']
        ]);
        $filterList = [$filter];
        $this->assertNotNull($this->object->findMatch($email, $filterList));

        $email = $this->createEntity('Email', Email::class, $this->emailDefs);
        $email->set('body', 'hello Man tester');
        $email->set('from', 'hello@test');

        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);
        $filter->set([
            'bodyContains' => ['man', 'red'],
            'from' => 'test@tester'
        ]);
        $filterList = [$filter];
        $this->assertNull($this->object->findMatch($email, $filterList));
    }

    public function testMatchEmpty(): void
    {
        $email = $this->createEntity('Email', Email::class, $this->emailDefs);
        $filter = $this->createEntity('EmailFilter', EmailFilter::class, $this->filterDefs);

        $email->set([
            'name' => 'Test',
            'from' => 'test@test.com',
            'to' => 'test@test.com',
            'body' => 'Test',
        ]);

        $filter->set([
            'subject' => '',
            'bodyContains' => [''],
            'from' => '',
            'to' => '',
        ]);

        $this->assertNull($this->object->findMatch($email, [$filter]));
    }
}
