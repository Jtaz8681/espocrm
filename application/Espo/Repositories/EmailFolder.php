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

/**
 * @extends \Espo\Core\Repositories\Database<\Espo\Entities\EmailFolder>
 */
class EmailFolder extends \Espo\Core\Repositories\Database
{
    protected function beforeSave(Entity $entity, array $options = [])
    {
        parent::beforeSave($entity, $options);

        $order = $entity->get('order');

        if (is_null($order)) {
            $order = $this->max('order');

            if (!$order) {
                $order = 0;
            }

            $order++;

            $entity->set('order', $order);
        }
    }
}
