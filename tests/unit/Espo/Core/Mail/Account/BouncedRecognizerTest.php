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

namespace tests\unit\Espo\Core\Mail\Account;

use Espo\Core\Mail\Account\GroupAccount\BouncedRecognizer;
use Espo\Core\Mail\MessageWrapper;
use Espo\Core\Mail\Parsers\MailMimeParser;

use Espo\ORM\EntityManager;

class BouncedRecognizerTest extends \PHPUnit\Framework\TestCase
{
    protected BouncedRecognizer $bouncedRecognizer;

    protected function setUp(): void
    {
        $this->bouncedRecognizer = new BouncedRecognizer();
    }

    private function createMessage(string $contents): MessageWrapper
    {
        $entityManager = $this->createMock(EntityManager::class);

        $parser = new MailMimeParser($entityManager);

        return new MessageWrapper(0, null, $parser, $contents);
    }

    public function testBounced1a(): void
    {
        $contents = file_get_contents('tests/unit/testData/Core/Mail/bounced_1.eml');

        $message = $this->createMessage($contents);

        $this->assertTrue($this->bouncedRecognizer->isBounced($message));
        $this->assertTrue($this->bouncedRecognizer->isHard($message));
        $this->assertEquals('0011aa', $this->bouncedRecognizer->extractQueueItemId($message));
    }

    public function testBounced1b(): void
    {
        $contents = file_get_contents('tests/unit/testData/Core/Mail/bounced_1.eml');
        $contents = str_replace('MAILER-DAEMON', 'test', $contents);

        $message = $this->createMessage($contents);

        $this->assertTrue($this->bouncedRecognizer->isBounced($message));
        $this->assertTrue($this->bouncedRecognizer->isHard($message));
    }

    public function testBounced2(): void
    {
        $contents = file_get_contents('tests/unit/testData/Core/Mail/bounced_2.eml');

        $message = $this->createMessage($contents);

        $this->assertTrue($this->bouncedRecognizer->isBounced($message));
        $this->assertFalse($this->bouncedRecognizer->isHard($message));
    }

    public function testNotBounced1(): void
    {
        $contents = file_get_contents('tests/unit/testData/Core/Mail/test_email_1.eml');

        $message = $this->createMessage($contents);

        $this->assertFalse($this->bouncedRecognizer->isBounced($message));
    }

    public function testBounced3(): void
    {
        $contents = file_get_contents('tests/unit/testData/Core/Mail/bounced_3.eml');

        $message = $this->createMessage($contents);

        $this->assertTrue($this->bouncedRecognizer->isBounced($message));
        $this->assertTrue($this->bouncedRecognizer->isHard($message));
        $this->assertEquals('5.4.1', $this->bouncedRecognizer->extractStatus($message));
    }
}
