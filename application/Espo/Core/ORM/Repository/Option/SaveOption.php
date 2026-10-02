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

namespace Espo\Core\ORM\Repository\Option;

use Espo\ORM\Repository\Option\SaveOption as BaseSaveOption;

/**
 * Save options.
 */
class SaveOption
{
    /**
     * Silent. Boolean.
     * Skip stream notes, notifications, webhooks.
     */
    public const SILENT = 'silent';
    /**
     * Import. Boolean.
     */
    public const IMPORT = 'import';
    /**
     * Called from a Record service.
     * @since 8.0.1
     */
    public const API = 'api';
    /**
     * Skip all additional processing. Boolean.
     */
    public const SKIP_ALL = BaseSaveOption::SKIP_ALL;
    /**
     * Keep new. Boolean.
     */
    public const KEEP_NEW = BaseSaveOption::KEEP_NEW;
    /**
     * Keep dirty. Boolean.
     */
    public const KEEP_DIRTY = BaseSaveOption::KEEP_DIRTY;
    /**
     * Keep an entity relations map. Boolean.
     * @since 9.0.0
     */
    public const KEEP_RELATIONS = BaseSaveOption::KEEP_RELATIONS;
    /**
     * Skip hooks. Boolean.
     */
    public const SKIP_HOOKS = 'skipHooks';
    /**
     * Skip setting created-by. Boolean.
     */
    public const SKIP_CREATED_BY = 'skipCreatedBy';
    /**
     * Skip setting modified-by. Boolean.
     */
    public const SKIP_MODIFIED_BY = 'skipModifiedBy';
    /**
     * Override created-by. String.
     */
    public const CREATED_BY_ID = 'createdById';
    /**
     * Override modified-by. String.
     */
    public const MODIFIED_BY_ID = 'modifiedById';
    /**
     * A duplicate source ID. A record that is being duplicated.
     * @since 8.4.0
     */
    public const DUPLICATE_SOURCE_ID = 'duplicateSourceId';

    /**
     * When saved in Mass-Update.
     * @since 8.4.0
     */
    public const MASS_UPDATE = 'massUpdate';
    /**
     * Skip stream notes. Boolean.
     * @since 9.0.0
     */
    public const NO_STREAM = 'noStream';
    /**
     * Skip notification. Boolean.
     * @since 9.0.0
     */
    public const NO_NOTIFICATIONS = 'noNotifications';

    /**
     * Skip audit log records.
     * @since 9.1.0
     */
    public const SKIP_AUDITED = 'skipAudited';
}
