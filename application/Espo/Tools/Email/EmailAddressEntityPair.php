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

use Espo\Core\Field\EmailAddress;
use Espo\Core\Name\Field;
use Espo\ORM\Entity;
use stdClass;

class EmailAddressEntityPair
{
    private EmailAddress $emailAddress;
    private Entity $entity;

    public function __construct(
        EmailAddress $emailAddress,
        Entity $entity
    ) {
        $this->emailAddress = $emailAddress;
        $this->entity = $entity;
    }

    public function getEmailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function getValueMap(): stdClass
    {
        return (object) [
            'emailAddress' => $this->emailAddress->getAddress(),
            'name' => $this->entity->get(Field::NAME),
            'entityId' => $this->entity->getId(),
            'entityType' => $this->entity->getEntityType(),
        ];
    }
}
