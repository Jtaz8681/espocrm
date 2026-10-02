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

namespace Espo\Core\Name;

class Field
{
    public const ID = 'id';
    public const NAME = 'name';
    public const CREATED_BY = 'createdBy';
    public const CREATED_AT = 'createdAt';
    public const MODIFIED_BY = 'modifiedBy';
    public const MODIFIED_AT = 'modifiedAt';
    public const STREAM_UPDATED_AT = 'streamUpdatedAt';
    public const ASSIGNED_USER = 'assignedUser';
    public const ASSIGNED_USERS = 'assignedUsers';
    public const COLLABORATORS = 'collaborators';
    public const TEAMS = 'teams';
    public const PARENT = 'parent';
    public const IS_FOLLOWED = 'isFollowed';
    public const FOLLOWERS = 'followers';
    public const IS_STARRED = 'isStarred';
    public const EMAIL_ADDRESS = 'emailAddress';
    public const PHONE_NUMBER = 'phoneNumber';
    public const VERSION_NUMBER = 'versionNumber';
    public const string IS_LOCKED = 'isLocked';

    /**
     * @since 10.0.0
     */
    public const string ACCOUNT = 'account';

    /**
     * @since 10.0.0
     */
    public const string PIPELINE = 'pipeline';

    /**
     * @since 10.0.0
     */
    public const string PIPELINE_STAGE = 'pipelineStage';
}
