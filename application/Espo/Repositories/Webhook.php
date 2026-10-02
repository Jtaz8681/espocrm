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

use Espo\ORM\Entity;
use Espo\Core\Utils\Util;
use Espo\Core\Repositories\Database;

/**
 * @extends Database<\Espo\Entities\Webhook>
 */
class Webhook extends Database
{
    protected function beforeSave(Entity $entity, array $options = [])
    {
        if ($entity->isNew()) {
            $this->fillSecretKey($entity);
        }

        parent::beforeSave($entity);

        $this->processSettingAdditionalFields($entity);
    }

    protected function fillSecretKey(Entity $entity): void
    {
        $secretKey = Util::generateSecretKey();

        $entity->set('secretKey', $secretKey);
    }

    protected function processSettingAdditionalFields(Entity $entity): void
    {
        $event = $entity->get('event');

        if (!$event) {
            return;
        }

        $arr = explode('.', $event);

        if (count($arr) !== 2 && count($arr) !== 3) {
            return;
        }

        $entityType = $arr[0];
        $type = $arr[1];

        $entity->set('entityType', $entityType);
        $entity->set('type', $type);

        $field = null;

        if (!$entityType) {
            return;
        }

        if ($type === 'fieldUpdate') {
            if (count($arr) == 3) {
                $field = $arr[2];
            }

            $entity->set('field', $field);
        } else {
            $entity->set('field', null);
        }
    }
}
