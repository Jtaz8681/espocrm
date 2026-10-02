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

namespace Espo\Core\Acl;

class Permission
{
    public const ASSIGNMENT = 'assignment';
    public const USER = 'user';
    public const PORTAL = 'portal';
    public const MASS_UPDATE = 'massUpdate';
    public const EXPORT = 'export';
    public const AUDIT = 'audit';
    public const DATA_PRIVACY = 'dataPrivacy';
    public const MESSAGE = 'message';
    public const MENTION = 'mention';
    public const USER_CALENDAR = 'userCalendar';
    public const FOLLOWER_MANAGEMENT = 'followerManagement';
    public const GROUP_EMAIL_ACCOUNT = 'groupEmailAccount';
    public const LOCK = 'lock';
}
