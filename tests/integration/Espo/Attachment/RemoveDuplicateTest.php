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

namespace tests\integration\Espo\Attachment;

class RemoveDuplicateTest extends \tests\integration\Core\BaseTestCase
{

    public function testRemoveDuplicate()
    {
        $entityManager = $this->getContainer()->get('entityManager');

        $fileStorageManager = $this->getContainer()->get('fileStorageManager');

        $attachment = $entityManager->getEntity('Attachment');

        $attachment->set([
            'name' => 'test.txt',
            'contents' => 'Hello Test'
        ]);

        $entityManager->saveEntity($attachment);

        $copy = $entityManager->getRepository('Attachment')->getCopiedAttachment($attachment);

        $entityManager->removeEntity($copy);

        $this->assertTrue($fileStorageManager->exists($attachment));
    }

    public function testRemoveOriginal()
    {
        $entityManager = $this->getContainer()->get('entityManager');

        $fileStorageManager = $this->getContainer()->get('fileStorageManager');

        $attachment = $entityManager->getEntity('Attachment');

        $attachment->set([
            'name' => 'test.txt',
            'contents' => 'Hello Test'
        ]);

        $entityManager->saveEntity($attachment);

        $copy = $entityManager->getRepository('Attachment')->getCopiedAttachment($attachment);

        $entityManager->removeEntity($attachment);

        $this->assertTrue($fileStorageManager->exists($copy));
    }

    public function testRemoveOriginalAndDuplicate()
    {
        $entityManager = $this->getContainer()->get('entityManager');

        $fileStorageManager = $this->getContainer()->get('fileStorageManager');

        $attachment = $entityManager->getEntity('Attachment');

        $attachment->set([
            'name' => 'test.txt',
            'contents' => 'Hello Test'
        ]);

        $entityManager->saveEntity($attachment);

        $copy = $entityManager->getRepository('Attachment')->getCopiedAttachment($attachment);

        $entityManager->removeEntity($attachment);
        $entityManager->removeEntity($copy);

        $this->assertFalse($fileStorageManager->exists($copy));
    }
}
