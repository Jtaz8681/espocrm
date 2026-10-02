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

namespace Espo\Entities;

use Espo\Core\ORM\Entity;
use stdClass;

class DashboardTemplate extends Entity
{
    public const string ENTITY_TYPE = 'DashboardTemplate';

    public const string FIELD_LAYOUT = 'layout';
    public const string FIELD_DASHLETS_OPTIONS = 'dashletsOptions';

    /**
     * @return array<int, mixed>
     * @since 10.0.0
     */
    public function getLayoutRaw(): array
    {
        /** @var array<int, mixed> */
        return $this->get(self::FIELD_LAYOUT) ?? [];
    }

    /**
     * @since 10.0.0
     */
    public function getDashletsOptionsRaw(): stdClass
    {
        return $this->get(self::FIELD_DASHLETS_OPTIONS) ?? (object) [];
    }
}
