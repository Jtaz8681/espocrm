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

namespace tests\integration\Espo\Tools\Object;

use Espo\Core\Name\Field;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\Modules\Crm\Entities\Task;
use Espo\Tools\Object\MetadataProvider;
use tests\integration\Core\BaseTestCase;

class MetadataProviderTest extends BaseTestCase
{
    public function testGetLinks(): void
    {
        $provider = $this->getInjectableFactory()->create(MetadataProvider::class);

        $this->assertEquals(Field::ACCOUNT, $provider->getAccountLink(Opportunity::ENTITY_TYPE));
        $this->assertEquals(Field::ACCOUNT, $provider->getAccountLink(Meeting::ENTITY_TYPE));

        $this->assertEquals(Field::PARENT, $provider->getParentLink(Meeting::ENTITY_TYPE));
        $this->assertEquals(Field::PARENT, $provider->getParentLink(Task::ENTITY_TYPE));
    }
}
