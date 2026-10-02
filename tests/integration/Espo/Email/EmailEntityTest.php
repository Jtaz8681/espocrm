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

class EmailEntityTest extends \tests\integration\Core\BaseTestCase
{
    public function testFromFields()
    {
        $entityManager = $this->getContainer()->get('entityManager');

        $email = $entityManager->getEntity('Email');
        $email->set('fromString', 'Test Hello <test@test.com>');

        $this->assertEquals('test@test.com', $email->get('fromAddress'));
        $this->assertEquals('Test Hello', $email->get('fromName'));
    }

    public function testReplyToFields()
    {
        $entityManager = $this->getContainer()->get('entityManager');

        $email = $entityManager->getEntity('Email');
        $email->set('replyToString', 'Test Hello <test@test.com>; Man Test <man@test.com>');

        $this->assertEquals('test@test.com', $email->get('replyToAddress'));
        $this->assertEquals('Test Hello', $email->get('replyToName'));
    }
}
