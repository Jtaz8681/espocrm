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

namespace Espo\Modules\Crm\Entities;

use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;

class CampaignLogRecord extends Entity
{
    public const ENTITY_TYPE = 'CampaignLogRecord';

    public const ACTION_LEAD_CREATED = 'Lead Created';
    public const ACTION_SENT = 'Sent';
    public const ACTION_BOUNCED = 'Bounced';
    public const ACTION_OPTED_IN = 'Opted In';
    public const ACTION_OPTED_OUT = 'Opted Out';
    public const ACTION_OPENED = 'Opened';
    public const ACTION_CLICKED = 'Clicked';

    public const BOUNCED_TYPE_HARD = 'Hard';
    public const BOUNCED_TYPE_SOFT = 'Soft';

    public function getParent(): ?LinkParent
    {
        /** @var ?LinkParent */
        return $this->getValueObject(Field::PARENT);
    }
}
