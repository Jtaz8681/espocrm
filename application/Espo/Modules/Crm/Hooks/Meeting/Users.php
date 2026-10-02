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
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Utils\Config;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<CoreEntity>
 */
class Users implements BeforeSave
{
    public static int $order = 12;

    public function __construct(
        private Config $config,
        private User $user
    ) {}

    /**
     * @param CoreEntity $entity
     */
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$this->config->get('eventAssignedUserIsAttendeeDisabled')) {
            if ($entity->hasLinkMultipleField(Field::ASSIGNED_USERS)) {
                $assignedUserIdList = $entity->getLinkMultipleIdList(Field::ASSIGNED_USERS);

                foreach ($assignedUserIdList as $assignedUserId) {
                    $entity->addLinkMultipleId('users', $assignedUserId);
                    $entity->setLinkMultipleName(
                        'users',
                        $assignedUserId,
                        $entity->getLinkMultipleName(Field::ASSIGNED_USERS, $assignedUserId)
                    );
                }
            } else {
                $assignedUserId = $entity->get('assignedUserId');

                if ($assignedUserId) {
                    $entity->addLinkMultipleId('users', $assignedUserId);
                    $entity->setLinkMultipleName('users', $assignedUserId, $entity->get('assignedUserName'));
                }
            }
        }

        if (!$entity->isNew()) {
            return;
        }

        $currentUserId = $this->user->getId();

        if (!$entity->hasLinkMultipleId('users', $currentUserId)) {
            return;
        }

        $status = $entity->getLinkMultipleColumn('users', 'status', $currentUserId);

        if (!$status || $status === Meeting::ATTENDEE_STATUS_NONE) {
            $entity->setLinkMultipleColumn('users', 'status', $currentUserId, Meeting::ATTENDEE_STATUS_ACCEPTED);
        }
    }
}
