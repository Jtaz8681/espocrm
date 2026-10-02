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

namespace Espo\Tools\EntityManager\Rename;

class FailReason
{
    public const ENV_NOT_SUPPORTED = 'envNotSupported';

    public const TABLE_EXISTS = 'tableExists';

    public const DOES_NOT_EXIST = 'doesNotExist';

    public const NOT_CUSTOM = 'notCustom';

    public const NAME_USED = 'nameUsed';

    public const NAME_NOT_ALLOWED = 'nameIsNotAllosed';

    public const NAME_BAD = 'nameBad';

    public const NAME_TOO_LONG = 'nameTooLong';

    public const NAME_TOO_SHORT = 'nameTooShort';

    public const ERROR = 'error';
}
