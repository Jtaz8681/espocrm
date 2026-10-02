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

namespace tests\integration\Espo\Tools\App;

use Espo\Tools\App\LanguageService;
use tests\integration\Core\BaseTestCase;

class LanguageTest extends BaseTestCase
{
    public function testAclDependency(): void
    {
        $this->createUser('tester', [
            'data' => [
                'Lead' => false,
                'Opportunity' => [
                    'create' => 'no',
                    'read' => 'own',
                    'edit' => 'no',
                    'delete' => 'no',
                    'stream' => 'no',
                ],
            ]
        ]);

        $this->authenticate('tester');

        $data = $this->getInjectableFactory()->create(LanguageService::class)->getDataForFrontend();

        $data = json_decode(json_encode($data));

        $this->assertNotNull($data?->Lead?->options?->source);
        $this->assertNull($data?->Lead?->options?->status ?? null);
    }
}
