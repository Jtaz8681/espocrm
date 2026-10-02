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
 * A field parameter.
 */
class FieldParam
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
     * Autoincrement.
     */
    public const AUTOINCREMENT = 'autoincrement';

    /**
     * A max length.
     */
    public const MAX_LENGTH = 'maxLength';

    /**
     * Not null.
     */
    public const NOT_NULL = 'notNull';

    /**
     * A default value.
     */
    public const DEFAULT = 'default';

    /**
     * Read-only.
     */
    public const READ_ONLY = 'readOnly';

    /**
     * Read-only after create.
     *
     * @since 10.0.0
     */
    public const READ_ONLY_AFTER_CREATE = 'readOnlyAfterCreate';

    /**
     * Decimal.
     */
    public const DECIMAL = 'decimal';

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

    /**
     * Foreign link.
     */
    public const LINK = 'link';

    /**
     * Foreign field.
     */
    public const FIELD = 'field';

    /**
     * Required.
     *
     * @since 9.3.0
     */
    public const REQUIRED = 'required';

    /**
     * Disabled.
     *
     * @since 9.3.0
     */
    public const DISABLED = 'disabled';

    /**
     * Utility. For internal purposes.
     *
     * @since 9.3.0
     */
    public const UTILITY = 'utility';

    /**
     * Min value.
     *
     * @since 9.3.0
     */
    public const MIN = 'min';

    /**
     * Max value.
     *
     * @since 9.3.0
     */
    public const MAX = 'max';
}
