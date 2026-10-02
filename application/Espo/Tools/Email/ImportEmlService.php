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

namespace Espo\Tools\Email;

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error;
use Espo\Core\FileStorage\Manager;
use Espo\Core\Mail\Exceptions\ImapError;
use Espo\Core\Mail\Importer;
use Espo\Core\Mail\Importer\Data;
use Espo\Core\Mail\MessageWrapper;
use Espo\Core\Mail\Parsers\MailMimeParser;
use Espo\Entities\Attachment;
use Espo\Entities\Email;
use Espo\ORM\EntityManager;
use RuntimeException;

class ImportEmlService
{
    public function __construct(
        private Importer $importer,
        private Importer\DuplicateFinder $duplicateFinder,
        private EntityManager $entityManager,
        private Manager $fileStorageManager,
        private MailMimeParser $parser,
    ) {}

    /**
     * Import an EML.
     *
     * @param ?string $userId A user ID to relate an email with.
     * @return Email An Email.
     * @throws Error
     * @throws Conflict
     */
    public function import(Attachment $attachment, ?string $userId = null): Email
    {
        $contents = $this->fileStorageManager->getContents($attachment);

        try {
            $message = new MessageWrapper(1, null, $this->parser, $contents);
        } catch (ImapError $e) {
            throw new RuntimeException(previous: $e);
        }

        $this->checkDuplicate($message);

        $email = $this->importer->import($message, Data::create());

        if (!$email) {
            throw new Error("Could not import.");
        }

        if ($userId) {
            $this->entityManager->getRDBRepositoryByClass(Email::class)
                ->getRelation($email, 'users')
                ->relateById($userId);
        }

        $this->entityManager->removeEntity($attachment);

        return $email;
    }

    /**
     * @throws Conflict
     */
    private function checkDuplicate(MessageWrapper $message): void
    {
        $messageId = $this->parser->getMessageId($message);

        if (!$messageId) {
            return;
        }

        $email = $this->entityManager->getRDBRepositoryByClass(Email::class)->getNew();
        $email->setMessageId($messageId);

        $duplicate = $this->duplicateFinder->find($email, $message);

        if (!$duplicate) {
            return;
        }

        throw Conflict::createWithBody(
            'Email is already imported.',
            Error\Body::create()->withMessageTranslation('alreadyImported', Email::ENTITY_TYPE, [
                'id' => $duplicate->getId(),
                'link' => '#Email/view/' . $duplicate->getId(),
            ])
        );
    }
}
