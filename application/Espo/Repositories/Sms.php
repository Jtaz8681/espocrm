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

namespace Espo\Repositories;

use Espo\Core\Name\Field;
use Espo\Entities\Sms as SmsEntity;
use Espo\Entities\PhoneNumber;

use Espo\Core\Repositories\Database;

/**
 * @extends Database<\Espo\Entities\Sms>
 */
class Sms extends Database
{
    public function loadFromField(SmsEntity $entity): void
    {
        if ($entity->get('fromPhoneNumberName')) {
            $entity->set('from', $entity->get('fromPhoneNumberName'));

            return;
        }

        $numberId = $entity->get('fromPhoneNumberId');

        if ($numberId) {
            $phoneNumber = $this->entityManager
                ->getRepository(PhoneNumber::ENTITY_TYPE)
                ->getById($numberId);

            if ($phoneNumber) {
                $entity->set('from', $phoneNumber->get(Field::NAME));

                return;
            }
        }

        $entity->set('from', null);
    }

    public function loadToField(SmsEntity $entity): void
    {
        $entity->loadLinkMultipleField('toPhoneNumbers');

        $names = $entity->get('toPhoneNumbersNames');

        if (empty($names)) {
            $entity->set('to', null);

            return;
        }

        $list = [];

        foreach ($names as $address) {
            $list[] = $address;
        }

        $entity->set('to', implode(';', $list));
    }
}
