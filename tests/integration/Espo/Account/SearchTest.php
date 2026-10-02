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

namespace tests\integration\Espo\Account;

use Espo\Core\Record\ServiceContainer;
use Espo\Core\Select\SearchParams;

class SearchTest extends \tests\integration\Core\BaseTestCase
{
    protected ?string $dataFile = 'Account/ChangeFields.php';

    protected ?string $userName = 'admin';
    protected ?string $password = '1';

    public function testSearchByName(): void
    {
        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->get('Account');

        $params = [
            'where' => [
                [
                    'type' => 'textFilter',
                    'value' => 'Besha',
                ],
            ],
            'offset' => 0,
            'maxSize' => 20,
            'asc' => true,
            'sortBy' => 'name',
        ];

        $result = $service->find(SearchParams::fromRaw($params));

        $this->assertEquals(1, $result->getTotal());

        $this->assertInstanceOf('Espo\\ORM\\EntityCollection', $result->getCollection());

        $list = $result->getCollection()->getValueMapList();

        $this->assertEquals('53203b942850b', $list[0]->id);
    }
}
