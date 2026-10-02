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

namespace tests\integration\Espo\Core\Utils;

class ClassFinderTest extends \tests\integration\Core\BaseTestCase
{
    public function testFind1()
    {
        $classFinder = $this->getContainer()->get('classFinder');

        $this->assertEquals(
            'Espo\\Modules\\Crm\\Entities\\Account',
            $classFinder->find('Entities', 'Account')
        );

        $this->assertEquals(
            'Espo\\Entities\\Email',
            $classFinder->find('Entities', 'Email')
        );

        $this->assertTrue(file_exists('data/cache/application/classmapEntities.php'));
    }
}
