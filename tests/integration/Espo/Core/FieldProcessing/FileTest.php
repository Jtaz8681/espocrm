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

namespace tests\integration\Espo\Core\FieldProcessing;

use Espo\Core\{
    ORM\EntityManager,
};

class FileTest extends \tests\integration\Core\BaseTestCase
{
    public function testFile1(): void
    {
        /* @var $entityManager EntityManager */
        $entityManager = $this->getContainer()->get('entityManager');

        $attachment1 = $entityManager->createEntity('Attachment', [
            'contents' => 'test-1',
            'relatedType' => 'Document',
        ]);

        $document = $entityManager->createEntity('Document', [
            'fileId' => $attachment1->getId(),
        ]);

        $attachment1 = $entityManager->getEntityById('Attachment', $attachment1->getId());

        $this->assertEquals($document->getId(), $attachment1->get('relatedId'));

        $attachment2 = $entityManager->createEntity('Attachment', [
            'contents' => 'test-2',
            'relatedType' => 'Document',
        ]);

        $document->set('fileId', $attachment2->getId());

        $entityManager->saveEntity($document);

        $attachment2 = $entityManager->getEntityById('Attachment', $attachment2->getId());

        $this->assertEquals($document->getId(), $attachment2->get('relatedId'));

        $attachment1 = $entityManager->getEntityById('Attachment', $attachment1->getId());

        $this->assertNull($attachment1);
    }
}
