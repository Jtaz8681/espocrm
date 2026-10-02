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

use Espo\Core\Utils\Metadata;
use Espo\Tools\App\MetadataService;
use tests\integration\Core\BaseTestCase;

class MetadataTest extends BaseTestCase
{
    public function testAclDependency(): void
    {
        $metadata = $this->getContainer()->getByClass(Metadata::class);

        $metadata->set('app', 'metadata', [
            'aclDependencies' => [
                'entityDefs.Campaign' => [
                    'anyScopeList' => ['Opportunity'],
                ],
            ],
        ]);

        $metadata->save();

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

        $data = $this->getInjectableFactory()->create(MetadataService::class)->getDataForFrontend();

        $this->assertIsArray($data?->entityDefs?->Lead?->fields?->source?->options);
        $this->assertNull($data->entityDefs->Lead->fields->name ?? null);
        $this->assertNotNull($data?->entityDefs->Campaign ?? null);
    }
}
