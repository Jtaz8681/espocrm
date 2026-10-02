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

namespace Espo\Modules\Crm\Business\Event;

use Espo\Core\Field\DateTime as DateTimeField;
use Espo\Core\Field\LinkParent;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Mail\Sender\AttachmentContainer;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Entities\Attachment;
use Espo\Entities\Email;
use Espo\Entities\Preferences;
use Espo\Entities\UniqueId;
use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;
use Espo\Entities\User;
use Espo\Core\Utils\Util;
use Espo\Core\Htmlizer\HtmlizerFactory as HtmlizerFactory;
use Espo\Core\Mail\EmailSender;
use Espo\Core\Mail\SmtpParams;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Core\Utils\Language;
use Espo\Core\Utils\TemplateFileManager;

use DateTime;

/**
 * Do not use. Use `Espo\Modules\Crm\Tools\Meeting\Invitation\Sender`.
 * @internal
 */
class Invitations
{
    private const TYPE_INVITATION = 'invitation';
    private const TYPE_CANCELLATION = 'cancellation';

    /**
     * Some dependencies are unused to keep backward compatibility.
     * @todo Revise.
     */
    public function __construct(
        private EntityManager $entityManager,
        private ?SmtpParams $smtpParams,
        private EmailSender $emailSender,
        private Language $language,
        private TemplateFileManager $templateFileManager,
        private HtmlizerFactory $htmlizerFactory,
        private ApplicationConfig $applicationConfig,
        private DateTimeUtil $dateTime,
    ) {}

    /**
     * @throws SendingError
     */
    public function sendInvitation(Entity $entity, Entity $invitee, string $link, ?string $emailAddress = null): void
    {
        $this->sendInternal($entity, $invitee, $link, self::TYPE_INVITATION, $emailAddress);
    }

    /**
     * @throws SendingError
     */
    public function sendCancellation(Entity $entity, Entity $invitee, string $link, ?string $emailAddress = null): void
    {
        $this->sendInternal($entity, $invitee, $link, self::TYPE_CANCELLATION, $emailAddress);
    }

    /**
     * @throws SendingError
     */
    private function sendInternal(
        Entity $entity,
        Entity $invitee,
        string $link,
        string $type,
        ?string $emailAddress,
    ): void {

        $uid = $type === self::TYPE_INVITATION ? $this->createUniqueId($entity, $invitee, $link) : null;

        /** @var ?string $emailAddress */
        $emailAddress ??= $invitee->get(Field::EMAIL_ADDRESS);

        if (!$emailAddress) {
            return;
        }

        $htmlizer = $invitee instanceof User ?
            $this->htmlizerFactory->createForUser($invitee) :
            $this->htmlizerFactory->createNoAcl();

        $data = $this->prepareData($entity, $uid, $invitee);

        $subjectTpl = $this->templateFileManager->getTemplate($type, 'subject', $entity->getEntityType());
        $subjectTpl = str_replace(["\n", "\r"], '', $subjectTpl);

        $bodyTpl = $this->templateFileManager->getTemplate($type, 'body', $entity->getEntityType());

        $subject = $htmlizer->render(
            $entity,
            $subjectTpl,
            $data,
            true,
            true
        );

        $body = $htmlizer->render(
            $entity,
            $bodyTpl,
            $data,
            false,
            true
        );

        $email = $this->entityManager->getRDBRepositoryByClass(Email::class)->getNew();

        $email
            ->addToAddress($emailAddress)
            ->setSubject($subject)
            ->setBody($body)
            ->setIsHtml()
            ->setParent(LinkParent::fromEntity($entity));

        $attachmentName = ucwords($this->language->translateLabel($entity->getEntityType(), 'scopeNames')) . '.ics';

        $attachment = $this->entityManager->getRDBRepositoryByClass(Attachment::class)->getNew();

        $attachment
            ->setName($attachmentName)
            ->setType('text/calendar')
            ->setContents($this->getIcsContents($entity, $type));

        $sender = $this->emailSender->create();

        if ($this->smtpParams) {
            $sender->withSmtpParams($this->smtpParams);
        }

        $method = 'REQUEST';

        if ($type === self::TYPE_CANCELLATION) {
            $method = 'CANCEL';
        }

        $container = new AttachmentContainer(
            attachment: $attachment,
            inline: true,
            contentTypeParams: [
                'charset' => 'utf-8',
                'method' => $method,
            ],
        );

        $sender
            ->withAttachments([$container])
            ->send($email);
    }

    private function createUniqueId(Entity $entity, Entity $invitee, string $link): UniqueId
    {
        $uid = $this->entityManager->getRDBRepositoryByClass(UniqueId::class)->getNew();

        $uid->setData([
            'eventType' => $entity->getEntityType(),
            'eventId' => $entity->getId(),
            'inviteeId' => $invitee->getId(),
            'inviteeType' => $invitee->getEntityType(),
            'link' => $link,
            'dateStart' => $entity->get('dateStart'),
        ]);

        if ($entity->get('dateEnd')) {
            $terminateAt = $entity->get('dateEnd');
        } else {
            $dt = new DateTime();
            $dt->modify('+1 month');

            $terminateAt = $dt->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
        }

        $uid->setTarget(LinkParent::fromEntity($entity));
        $uid->setTerminateAt(DateTimeField::fromString($terminateAt));

        $this->entityManager->saveEntity($uid);

        return $uid;
    }

    protected function getIcsContents(Entity $entity, string $type): string
    {
        /** @var ?User $user */
        $user = $this->entityManager
            ->getRelation($entity, Field::ASSIGNED_USER)
            ->findOne();

        $addressList = [];

        $organizerName = null;
        $organizerAddress = null;

        if ($user) {
            $organizerName = $user->getName();
            $organizerAddress = $user->getEmailAddress();

            /*if ($organizerAddress) {
                $addressList[] = $organizerAddress;
            }*/
        }

        $status = $type === self::TYPE_CANCELLATION ?
            Ics::STATUS_CANCELLED :
            Ics::STATUS_CONFIRMED;

        $method = $type === self::TYPE_CANCELLATION ?
            Ics::METHOD_CANCEL :
            Ics::METHOD_REQUEST;

        $attendees = [];

        $uid = $entity->getId();

        if ($entity instanceof Meeting || $entity instanceof Call) {
            $attendees = $this->getAttendees($entity, $addressList);

            $uid = $entity->getUid() ?? $uid;
        }

        $ics = new Ics('//BugZyro//BugZyro Calendar//EN', [
            'method' => $method,
            'status' => $status,
            'startDate' => strtotime($entity->get('dateStart')),
            'endDate' => strtotime($entity->get('dateEnd')),
            'uid' => $uid,
            'summary' => $entity->get(Field::NAME),
            'organizer' => $organizerAddress ? [$organizerAddress, $organizerName] : null,
            'attendees' => $attendees,
            'description' => $entity->get('description'),
        ]);

        return $ics->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareData(Entity $entity, ?UniqueId $uid, Entity $invitee): array
    {
        $data = [];

        $siteUrl = $this->applicationConfig->getSiteUrl();

        $data['recordUrl'] = "$siteUrl/#{$entity->getEntityType()}/view/{$entity->getId()}";

        if ($uid) {
            $part = "$siteUrl?entryPoint=eventConfirmation&action=";

            $data['acceptLink'] = $part . 'accept&uid=' . $uid->getIdValue();
            $data['declineLink'] = $part . 'decline&uid=' . $uid->getIdValue();
            $data['tentativeLink'] = $part . 'tentative&uid=' . $uid->getIdValue();
        }

        if ($invitee instanceof User) {
            $data['isUser'] = true;
        }

        $data['inviteeName'] = $invitee->get(Field::NAME);
        $data['entityType'] = $this->language->translateLabel($entity->getEntityType(), 'scopeNames');
        $data['entityTypeLowerFirst'] = Util::mbLowerCaseFirst($data['entityType']);

        [$timeZone, $language] = $this->getTimeZoneAndLanguage($invitee);

        $data['timeZone'] = $timeZone;
        $data['dateStartFull'] = $this->prepareDateStartFull($entity, $timeZone, $language);

        return $data;
    }

    /**
     * @param string[] $addressList
     * @return array{string, ?string, ?string}[]
     */
    private function getAttendees(Meeting|Call $entity, array $addressList): array
    {
        $attendees = [];

        /** @var iterable<User> $users */
        $users = $this->entityManager
            ->getRelation($entity, Meeting::LINK_USERS)
            ->find();

        foreach ($users as $it) {
            $address = $it->getEmailAddress();

            if ($address && !in_array($address, $addressList)) {
                $addressList[] = $address;
                $attendees[] = [$address, $it->getName(), $this->getStatus($it)];
            }
        }

        /** @var iterable<Contact> $contacts */
        $contacts = $this->entityManager
            ->getRelation($entity, Meeting::LINK_CONTACTS)
            ->find();

        foreach ($contacts as $it) {
            $address = $it->getEmailAddress();

            if ($address && !in_array($address, $addressList)) {
                $addressList[] = $address;
                $attendees[] = [$address, $it->getName(), $this->getStatus($it)];
            }
        }

        /** @var iterable<Lead> $leads */
        $leads = $this->entityManager
            ->getRelation($entity, Meeting::LINK_LEADS)
            ->find();

        foreach ($leads as $it) {
            $address = $it->getEmailAddress();

            if ($address && !in_array($address, $addressList)) {
                $addressList[] = $address;
                $attendees[] = [$address, $it->getName(), $this->getStatus($it)];
            }
        }

        return $attendees;
    }

    /**
     * @return array{string, string}
     */
    private function getTimeZoneAndLanguage(Entity $invitee): array
    {
        $timeZone = $this->applicationConfig->getTimeZone();
        $language = $this->applicationConfig->getLanguage();

        if ($invitee instanceof User) {
            $preferences = $this->entityManager
                ->getRepositoryByClass(Preferences::class)
                ->getById($invitee->getId());

            if ($preferences && $preferences->getTimeZone()) {
                $timeZone = $preferences->getTimeZone();
            }

            if ($preferences && $preferences->getLanguage()) {
                $language = $preferences->getLanguage();
            }
        }

        return [$timeZone, $language];
    }

    /**
     * @todo Take into account the invitees time format if a user.
     */
    private function prepareDateStartFull(Entity $entity, string $timeZone, string $language): ?string
    {
        $format = "dddd, MMMM Do, YYYY";

        if ($entity->get('dateStartDate')) {
            $value = $entity->get('dateStartDate');

            return $this->dateTime->convertSystemDate($value, $format, $language);
        }

        $value = $entity->get('dateStart');

        if (!$value) {
            return null;
        }

        $format = $this->applicationConfig->getTimeFormat() . ", " . $format;

        return $this->dateTime->convertSystemDateTime($value, $timeZone, $format, $language);
    }

    private function getStatus(User|Contact|Lead $invitee): ?string
    {
        $status = $invitee->get('acceptanceStatus');

        return match ($status) {
            Meeting::ATTENDEE_STATUS_ACCEPTED => 'ACCEPTED',
            Meeting::ATTENDEE_STATUS_DECLINED => 'DECLINED',
            Meeting::ATTENDEE_STATUS_TENTATIVE => 'TENTATIVE',
            default => null,
        };
    }
}
