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

namespace Espo\Core\Portal\Acl\Map;

use Espo\Entities\Portal;
use Espo\Entities\User;
use Espo\Core\Acl\Map\CacheKeyProvider as CacheKeyProviderInterface;

class CacheKeyProvider implements CacheKeyProviderInterface
{
    public function __construct(private User $user, private Portal $portal)
    {}

    public function get(): string
    {
        return 'aclPortalMap/' . $this->portal->getId() . '/' . $this->user->getId();
    }
}
