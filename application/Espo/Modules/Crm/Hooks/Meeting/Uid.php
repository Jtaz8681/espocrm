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

namespace Espo\Modules\Crm\Hooks\Meeting;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Mail\Event\EventFactory;
use Espo\Core\Utils\Util;
use Espo\Entities\Email;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;
use ICal\ICal;

/**
 * @implements BeforeSave<Meeting>
 */
class Uid implements BeforeSave
{
    public function __construct(private EntityManager $entityManager) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew() || $entity->getUid()) {
            return;
        }

        $uid = $this->getUid($entity);

        $entity->setUid($uid);
    }

    private function getUid(Meeting $entity): string
    {
        $uid = $this->getIcsUid($entity);

        if ($uid) {
            return $uid;
        }

        return Util::generateUuid4();
    }

    private function getIcsUid(Meeting $entity): ?string
    {
        $email = $this->getEmail($entity);

        if (!$email) {
            return null;
        }

        $icsContents = $email->getIcsContents();

        if (!$icsContents) {
            return null;
        }

        $ical = new ICal();

        $ical->initString($icsContents);

        $espoEvent = EventFactory::createFromU01jmg3Ical($ical);

        return $espoEvent->getUid();
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
