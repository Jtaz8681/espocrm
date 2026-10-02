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

namespace Espo\Modules\Crm\Tools\Calendar\Items;

use Espo\Core\Field\DateTime;
use Espo\Modules\Crm\Tools\Calendar\Item;
use stdClass;

class NonWorkingRange implements Item
{
    private DateTime $start;
    private DateTime $end;

    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    public function getRaw(): stdClass
    {
        return (object) [
            'dateStart' => $this->start->toString(),
            'dateEnd' => $this->end->toString(),
            'isNonWorkingRange' => true,
        ];
    }
}
