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

/**
 * Access levels for a user.
 */
interface Table
{
    public const LEVEL_YES = 'yes';
    public const LEVEL_NO = 'no';
    public const LEVEL_ALL = 'all';
    public const LEVEL_TEAM = 'team';
    public const LEVEL_OWN = 'own';

    public const ACTION_READ = 'read';
    public const ACTION_STREAM = 'stream';
    public const ACTION_EDIT = 'edit';
    public const ACTION_DELETE = 'delete';
    public const ACTION_CREATE = 'create';

    /**
     * Get scope data.
     */
    public function getScopeData(string $scope): ScopeData;

    /**
     * Get field data.
     */
    public function getFieldData(string $scope, string $field): FieldData;

    /**
     * Get a permission level.
     *
     * @return self::ACTION_*
     */
    public function getPermissionLevel(string $permission): string;
}
