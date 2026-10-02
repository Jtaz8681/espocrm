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

namespace Espo\Modules\Crm\Classes\FieldProcessing\Meeting;

use Espo\Entities\Email;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Core\FieldProcessing\Saver;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\Mail\Event\EventFactory;

use ICal\ICal;

/**
 * @implements Saver<Meeting>
 */
class SourceEmailSaver implements Saver
{
    public function __construct(private EntityManager $entityManager)
    {}

    /**
     * @param Meeting $entity
     */
    public function process(Entity $entity, Params $params): void
    {
        if (!$entity->isNew()) {
            return;
        }

        $email = $this->getEmail($entity);

        if (!$email) {
            return;
        }

        $icsContents = $email->getIcsContents();

        if ($icsContents === null) {
            return;
        }

        $ical = new ICal();

        $ical->initString($icsContents);

        $espoEvent = EventFactory::createFromU01jmg3Ical($ical);

        $email->set('createdEventId', $entity->getId());
        $email->set('createdEventType', $entity->getEntityType());
        $email->set('icsEventUid', $espoEvent->getUid());

        $this->entityManager->saveEntity($email);
    }

    private function getEmail(Meeting $entity): ?Email
    {
        $emailId = $entity->get('sourceEmailId');

        if (!$emailId) {
            return null;
        }

        return $this->entityManager->getRDBRepositoryByClass(Email::class)->getById($emailId);
    }
}
