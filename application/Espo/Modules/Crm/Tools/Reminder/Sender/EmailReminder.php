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

namespace Espo\Modules\Crm\Tools\Reminder\Sender;

use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Entities\Reminder;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Utils\Util;
use Espo\Core\Htmlizer\HtmlizerFactory as HtmlizerFactory;
use Espo\Core\Mail\EmailSender;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Language;
use Espo\Core\Utils\TemplateFileManager;
use RuntimeException;

class EmailReminder
{
    public function __construct(
        private EntityManager $entityManager,
        private TemplateFileManager $templateFileManager,
        private EmailSender $emailSender,
        private Config $config,
        private HtmlizerFactory $htmlizerFactory,
        private Language $language
    ) {}

    /**
     * @throws SendingError
     */
    public function send(Reminder $reminder): void
    {
        $entityType = $reminder->getTargetEntityType();
        $entityId = $reminder->getTargetEntityId();
        $userId = $reminder->getUserId();

        if (!$entityType || !$entityId || !$userId) {
            throw new RuntimeException("Bad reminder.");
        }

        $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);
        $entity = $this->entityManager->getEntityById($entityType, $entityId);

        if (
            !$user ||
            !$entity instanceof CoreEntity ||
            !$user->getEmailAddress()
        ) {
            return;
        }

        if (
            $entity->hasLinkMultipleField('users') &&
            $entity->hasAttribute('usersColumns')
        ) {
            $status = $entity->getLinkMultipleColumn('users', 'status', $user->getId());

            if ($status === Meeting::ATTENDEE_STATUS_DECLINED) {
                return;
            }
        }

        [$subject, $body] = $this->getSubjectBody($entity, $user);

        $email = $this->entityManager->getRDBRepositoryByClass(Email::class)->getNew();

        $email->addToAddress($user->getEmailAddress());
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setIsHtml();

        $this->emailSender->send($email);
    }

    /**
     * @return array{string, string}
     */
    private function getTemplates(CoreEntity $entity): array
    {
        $subjectTpl = $this->templateFileManager
            ->getTemplate('reminder', 'subject', $entity->getEntityType());

        $bodyTpl = $this->templateFileManager
            ->getTemplate('reminder', 'body', $entity->getEntityType());

        return [$subjectTpl, $bodyTpl];
    }

    /**
     * @return array{string, string}
     */
    private function getSubjectBody(CoreEntity $entity, User $user): array
    {
        $entityType = $entity->getEntityType();
        $entityId = $entity->getId();

        [$subjectTpl, $bodyTpl] = $this->getTemplates($entity);

        $subjectTpl = str_replace(["\n", "\r"], '', $subjectTpl);

        $siteUrl = rtrim($this->config->get('siteUrl'), '/');
        $translatedEntityType = $this->language->translateLabel($entityType, 'scopeNames');

        $data = [
            'recordUrl' => "$siteUrl/#$entityType/view/$entityId",
            'entityType' => $translatedEntityType,
            'entityTypeLowerFirst' => Util::mbLowerCaseFirst($translatedEntityType),
            'userName' => $user->getName(),
        ];

        $htmlizer = $this->htmlizerFactory->createForUser($user);

        $subject = $htmlizer->render(
            $entity,
            $subjectTpl,
            $data,
            true
        );

        $body = $htmlizer->render(
            $entity,
            $bodyTpl,
            $data,
            false
        );

        return [$subject, $body];
    }
}
