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

namespace Espo\Core\Select\Where\Item;

class Type
{
    public const AND = 'and';
    public const OR = 'or';
    public const NOT = 'not';
    public const SUBQUERY_NOT_IN = 'subQueryNotIn';
    public const SUBQUERY_IN = 'subQueryIn';
    public const EXPRESSION = 'expression';
    public const IN = 'in';
    public const NOT_IN = 'notIn';
    public const EQUALS = 'equals';
    public const NOT_EQUALS = 'notEquals';
    public const ON = 'on';
    public const NOT_ON = 'notOn';
    public const LIKE = 'like';
    public const NOT_LIKE = 'notLike';
    public const STARTS_WITH = 'startsWith';
    public const ENDS_WITH = 'endsWith';
    public const CONTAINS = 'contains';
    public const NOT_CONTAINS = 'notContains';
    public const GREATER_THAN = 'greaterThan';
    public const LESS_THAN = 'lessThan';
    public const GREATER_THAN_OR_EQUALS = 'greaterThanOrEquals';
    public const LESS_THAN_OR_EQUALS = 'lessThanOrEquals';
    public const AFTER = 'after';
    public const BEFORE = 'before';
    public const BETWEEN = 'between';
    public const EVER = 'ever';
    public const ANY = 'any';
    public const NONE = 'none';
    public const IS_NULL = 'isNull';
    public const IS_NOT_NULL = 'isNotNull';
    public const IS_TRUE = 'isTrue';
    public const IS_FALSE = 'isFalse';
    public const TODAY = 'today';
    public const PAST = 'past';
    public const FUTURE = 'future';
    public const LAST_SEVEN_DAYS = 'lastSevenDays';
    public const LAST_X_DAYS = 'lastXDays';
    public const NEXT_X_DAYS = 'nextXDays';
    public const OLDER_THAN_X_DAYS = 'olderThanXDays';
    public const AFTER_X_DAYS = 'afterXDays';
    public const CURRENT_MONTH = 'currentMonth';
    public const NEXT_MONTH = 'nextMonth';
    public const LAST_MONTH = 'lastMonth';
    public const CURRENT_QUARTER = 'currentQuarter';
    public const LAST_QUARTER = 'lastQuarter';
    public const CURRENT_YEAR = 'currentYear';
    public const LAST_YEAR = 'lastYear';
    public const CURRENT_FISCAL_YEAR = 'currentFiscalYear';
    public const LAST_FISCAL_YEAR = 'lastFiscalYear';
    public const CURRENT_FISCAL_QUARTER = 'currentFiscalQuarter';
    public const LAST_FISCAL_QUARTER = 'lastFiscalQuarter';
    public const ARRAY_ANY_OF = 'arrayAnyOf';
    public const ARRAY_NONE_OF = 'arrayNoneOf';
    public const ARRAY_ALL_OF = 'arrayAllOf';
    public const ARRAY_IS_EMPTY = 'arrayIsEmpty';
    public const ARRAY_IS_NOT_EMPTY = 'arrayIsNotEmpty';

    /**
     * Is linked with at least one of provided values (Any Of).
     */
    public const string IS_LINKED_WITH = 'linkedWith';

    /**
     * Is not linked with any of provided values (None Of).
     */
    public const string IS_NOT_LINKED_WITH = 'notLinkedWith';

    /**
     * Is linked with all of provided values (All Of).
     */
    public const string IS_LINKED_WITH_ALL = 'linkedWithAll';

    /**
     * Is linked with anything (Is Not Empty).
     */
    public const string IS_LINKED_WITH_ANY = 'isLinked';


    /**
     * Is not linked with anything (Is Empty).
     */
    public const string IS_LINKED_WITH_NONE = 'isNotLinked';
}
