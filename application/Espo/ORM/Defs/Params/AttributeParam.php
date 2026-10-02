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
 * An attribute parameter.
 */
class AttributeParam
{
    /**
     * A type.
     */
    public const TYPE = 'type';

    /**
     * Not stored in database.
     */
    public const NOT_STORABLE = 'notStorable';

    /**
     * A database type.
     */
    public const DB_TYPE = 'dbType';

    /**
     * A length.
     */
    public const LEN = 'len';

    /**
     * Not null.
     */
    public const NOT_NULL = 'notNull';

    /**
     * Autoincrement.
     */
    public const AUTOINCREMENT = 'autoincrement';

    /**
     * A default value.
     */
    public const DEFAULT = 'default';

    /**
     * A relation. For foreign attributes.
     */
    public const RELATION = 'relation';

    /**
     * A foreign attribute name. For foreign attributes.
     */
    public const FOREIGN = 'foreign';

    /**
     * Precision.
     */
    public const PRECISION = 'precision';

    /**
     * Scale.
     */
    public const SCALE = 'scale';

    /**
     * Dependee attributes.
     */
    public const DEPENDEE_ATTRIBUTE_LIST = 'dependeeAttributeList';
}
