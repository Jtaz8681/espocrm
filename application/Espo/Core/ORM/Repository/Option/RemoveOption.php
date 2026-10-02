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

/**
 * Save options.
 *
 * @since 9.2.0
 */
class RemoveOption
{
    /**
     * Silent. Boolean.
     * Skip stream notes, notifications, webhooks.
     */
    public const SILENT = 'silent';

    /**
     * Called from a Record service.
     */
    public const API = 'api';

    /**
     * When saved in Mass-Remove.
     */
    public const MASS_REMOVE = 'massRemove';

    /**
     * Override modified-by. String.
     */
    public const MODIFIED_BY_ID = 'modifiedById';
}
