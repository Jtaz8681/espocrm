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

namespace Espo\Modules\Crm\Classes\Pdf\Account;

use Espo\Tools\Pdf\Data\DataLoader;
use Espo\Tools\Pdf\Params;
use Espo\ORM\Entity;

use stdClass;

class ExampleDataLoader implements DataLoader
{
    public function load(Entity $entity, Params $params): stdClass
    {
        // Here you can load additional data for PDF;

        return (object) [

        ];
    }
}
