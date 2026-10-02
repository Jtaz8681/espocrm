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

namespace Espo\Classes\FieldProcessing\Email;

use Espo\Core\Name\Field;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Repositories\EmailAddress as EmailAddressRepository;
use Espo\Entities\EmailAddress;
use Espo\Entities\Email;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\Mail\Event\Event as EspoEvent;
use Espo\Core\Mail\Event\EventFactory;
use Espo\Core\Utils\Log;

use ICal\Event;
use ICal\ICal;

use Throwable;
use stdClass;

/**
 * @implements Loader<Email>
 */
class IcsDataLoader implements Loader
{
    /** @var array<string, string> */
    private $entityTypeLinkMap = [
        User::ENTITY_TYPE => Meeting::LINK_USERS,
        Contact::ENTITY_TYPE => Meeting::LINK_CONTACTS,
        Lead::ENTITY_TYPE => Meeting::LINK_LEADS,
    ];

    public function __construct(private EntityManager $entityManager, private Log $log)
    {}

    public function process(Entity $entity, Params $params): void
    {
        $icsContents = $entity->get('icsContents');

        if ($icsContents === null) {
            return;
        }

        $ical = new ICal();

        $ical->initString($icsContents);

        /* @var ?Event $event */
        $event = $ical->events()[0] ?? null;

        if ($event === null) {
            return;
        }

        if ($event->status === 'CANCELLED') {
            return;
        }

        $method = $ical->cal['VCALENDAR']['METHOD'] ?? null;

        if ($method === 'REPLY') {
            return;
        }

        $espoEvent = EventFactory::createFromU01jmg3Ical($ical);

        $valueMap = (object) [
            'sourceEmailId' => $entity->getId(),
        ];

        try {
            $valueMap->name = $espoEvent->getName();
            $valueMap->description = $espoEvent->getDescription();
            $valueMap->dateStart = $espoEvent->getDateStart();
            $valueMap->dateEnd = $espoEvent->getDateEnd();
            $valueMap->location = $espoEvent->getLocation();
            $valueMap->isAllDay = $espoEvent->isAllDay();

            if ($espoEvent->isAllDay()) {
                $valueMap->dateStartDate = $espoEvent->getDateStart();
                $valueMap->dateEndDate = $espoEvent->getDateEnd();
            }
        } catch (Throwable $e) {
            $this->log->warning("Error while converting ICS event '" . $entity->getId() . "': " . $e->getMessage());

            return;
        }

        if ($this->eventAlreadyExists($espoEvent)) {
            return;
        }

        /** @var EmailAddressRepository $emailAddressRepository */
        $emailAddressRepository = $this->entityManager->getRepository(EmailAddress::ENTITY_TYPE);

        $attendeeEmailAddressList = $espoEvent->getAttendeeEmailAddressList();
        $organizerEmailAddress = $espoEvent->getOrganizerEmailAddress();

        if ($organizerEmailAddress) {
            $attendeeEmailAddressList[] = $organizerEmailAddress;
        }

        foreach ($attendeeEmailAddressList as $address) {
            $personEntity = $emailAddressRepository->getEntityByAddress($address);

            if (!$personEntity) {
                continue;
            }

            $link = $this->entityTypeLinkMap[$personEntity->getEntityType()] ?? null;

            if (!$link) {
                continue;
            }

            $idsAttribute = $link . 'Ids';
            $namesAttribute = $link . 'Names';

            $idList = $valueMap->$idsAttribute ?? [];
            $nameMap = $valueMap->$namesAttribute ?? (object) [];

            $idList[] = $personEntity->getId();
            $nameMap->{$personEntity->getId()} = $personEntity->get(Field::NAME);

            $valueMap->$idsAttribute = $idList;
            $valueMap->$namesAttribute = $nameMap;
        }

        $eventData = (object) [
            'valueMap' => $valueMap,
            'uid' => $espoEvent->getUid(),
            'createdEvent' => null,
        ];

        $this->loadCreatedEvent($entity, $espoEvent, $eventData);

        $entity->set('icsEventData', $eventData);
        $entity->set('icsEventDateStart', $espoEvent->getDateStart());

        if ($espoEvent->isAllDay()) {
            $entity->set('icsEventDateStartDate', $espoEvent->getDateStart());
        }
    }

    private function loadCreatedEvent(Entity $entity, EspoEvent $espoEvent, stdClass $eventData): void
    {
        $emailSameEvent = $this->entityManager
            ->getRDBRepository(Email::ENTITY_TYPE)
            ->where([
                'icsEventUid' => $espoEvent->getUid(),
                'id!=' => $entity->getId()
            ])
            ->findOne();

        if (!$emailSameEvent) {
            return;
        }

        if (
            !$emailSameEvent->get('createdEventId') ||
            !$emailSameEvent->get('createdEventType')
        ) {
            return;
        }

        $createdEvent = $this->entityManager
            ->getEntityById($emailSameEvent->get('createdEventType'), $emailSameEvent->get('createdEventId'));

        if (!$createdEvent) {
            return;
        }

        $eventData->createdEvent = (object) [
            'id' => $createdEvent->getId(),
            'entityType' => $emailSameEvent->getEntityType(),
            'name' => $createdEvent->get(Field::NAME),
        ];
    }

    private function eventAlreadyExists(EspoEvent $espoEvent): bool
    {
        $id = $espoEvent->getUid();

        if (!$id) {
            return false;
        }

        $found1 = $this->entityManager
            ->getRDBRepository(Meeting::ENTITY_TYPE)
            ->select([Attribute::ID])
            ->where([Attribute::ID => $id])
            ->findOne();

        if ($found1) {
            return true;
        }

        $found2 = $this->entityManager
            ->getRDBRepository(Call::ENTITY_TYPE)
            ->select([Attribute::ID])
            ->where([Attribute::ID => $id])
            ->findOne();

        if ($found2) {
            return true;
        }

        return false;
    }
}
