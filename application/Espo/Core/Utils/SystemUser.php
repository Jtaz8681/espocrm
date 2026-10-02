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

namespace Espo\Core\Utils;

/**
 * A system user utility.
 */
class SystemUser
{
    /**
     * A system user username.
     */
    public const NAME = 'system';

    private const ID = 'system';
    private const UUID = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    private string $id;

    public function __construct(Metadata $metadata, Config $config)
    {
        $id = $config->get('systemUserId');

        if ($id) {
            $this->id = $id;

            return;
        }

        $isUuid = $metadata->get(['app', 'recordId', 'dbType']) === 'uuid';

        $this->id = $isUuid ?
            self::UUID :
            self::ID;
    }

    /**
     * Get a system user ID.
     */
    public function getId(): string
    {
        return $this->id;
    }
}
