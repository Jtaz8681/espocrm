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

namespace Espo\ORM\Defs\Params;

/**
 * A relation parameter.
 */
class RelationParam
{
    /**
     * A type.
     */
    public const TYPE = 'type';

    /**
     * Indexes.
     */
    public const INDEXES = 'indexes';

    /**
     * A relation name.
     */
    public const RELATION_NAME = 'relationName';

    /**
     * A foreign entity type.
     */
    public const ENTITY = 'entity';

    /**
     * A foreign relation name.
     */
    public const FOREIGN = 'foreign';

    /**
     * Conditions.
     */
    public const CONDITIONS = 'conditions';

    /**
     * Additional columns.
     */
    public const ADDITIONAL_COLUMNS = 'additionalColumns';

    /**
     * A key.
     */
    public const KEY = 'key';

    /**
     * A foreign key.
     */
    public const FOREIGN_KEY = 'foreignKey';

    /**
     * Middle keys.
     */
    public const MID_KEYS = 'midKeys';

    /**
     * No join.
     */
    public const NO_JOIN = 'noJoin';

    /**
     * Deferred load.
     */
    public const DEFERRED_LOAD = 'deferredLoad';

    /**
     * Default order by. Applied on the entity level.
     *
     * @since 9.2.5
     */
    public const ORDER_BY = 'orderBy';

    /**
     * Default order. Applied on the entity level.
     *
     * @since 9.2.5
     */
    public const ORDER = 'order';

    /**
     * @since 10.0.0
     */
    public const READ_ONLY = 'readOnly';

    /**
     * Disabled.
     *
     * @since 10.0.0
     */
    public const DISABLED = 'disabled';

    /**
     * Cascade removal Only for one-to-many, one-to-one, and parent-to-children.
     *
     * @since 10.0.0
     */
    public const string CASCADE_REMOVAL = 'cascadeRemoval';
}
