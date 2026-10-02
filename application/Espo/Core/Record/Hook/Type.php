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

namespace Espo\Core\Record\Hook;

class Type
{
    public const BEFORE_READ = 'beforeRead';

    public const EARLY_BEFORE_CREATE = 'earlyBeforeCreate';
    public const EARLY_BEFORE_UPDATE = 'earlyBeforeUpdate';

    public const BEFORE_CREATE = 'beforeCreate';
    public const BEFORE_UPDATE = 'beforeUpdate';
    public const BEFORE_DELETE = 'beforeDelete';

    public const AFTER_CREATE = 'afterCreate';
    public const AFTER_UPDATE = 'afterUpdate';
    public const AFTER_DELETE = 'afterDelete';

    public const BEFORE_LINK = 'beforeLink';
    public const BEFORE_UNLINK = 'beforeUnlink';
    public const AFTER_LINK = 'afterLink';
    public const AFTER_UNLINK = 'afterUnlink';
}
