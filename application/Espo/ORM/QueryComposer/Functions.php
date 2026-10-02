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

namespace Espo\ORM\QueryComposer;

/**
 * @internal
 */
class Functions
{
    public const FUNCTION_LIST = [
        'ROW',
        'COUNT',
        'SUM',
        'AVG',
        'MAX',
        'MIN',
        'DATE',
        'MONTH',
        'DAY',
        'YEAR',
        'WEEK',
        'WEEK_0',
        'WEEK_1',
        'QUARTER',
        'DAYOFMONTH',
        'DAYOFWEEK',
        'DAYOFWEEK_NUMBER',
        'MONTH_NUMBER',
        'DATE_NUMBER',
        'YEAR_NUMBER',
        'HOUR_NUMBER',
        'HOUR',
        'MINUTE_NUMBER',
        'MINUTE',
        'QUARTER_NUMBER',
        'WEEK_NUMBER',
        'WEEK_NUMBER_0',
        'WEEK_NUMBER_1',
        'LOWER',
        'UPPER',
        'TRIM',
        'REPLACE',
        'LENGTH',
        'CHAR_LENGTH',
        'YEAR_0',
        'YEAR_1',
        'YEAR_2',
        'YEAR_3',
        'YEAR_4',
        'YEAR_5',
        'YEAR_6',
        'YEAR_7',
        'YEAR_8',
        'YEAR_9',
        'YEAR_10',
        'YEAR_11',
        'QUARTER_0',
        'QUARTER_1',
        'QUARTER_2',
        'QUARTER_3',
        'QUARTER_4',
        'QUARTER_5',
        'QUARTER_6',
        'QUARTER_7',
        'QUARTER_8',
        'QUARTER_9',
        'QUARTER_10',
        'QUARTER_11',
        'CONCAT',
        'LEFT',
        'TZ',
        'NOW',
        'ADD',
        'SUB',
        'MUL',
        'DIV',
        'MOD',
        'FLOOR',
        'CEIL',
        'ROUND',
        'GREATEST',
        'LEAST',
        'COALESCE',
        'IF',
        'LIKE',
        'NOT_LIKE',
        'EQUAL',
        'NOT_EQUAL',
        'GREATER_THAN',
        'LESS_THAN',
        'GREATER_THAN_OR_EQUAL',
        'LESS_THAN_OR_EQUAL',
        'IS_NULL',
        'IS_NOT_NULL',
        'OR',
        'AND',
        'NOT',
        'IN',
        'NOT_IN',
        'IFNULL',
        'NULLIF',
        'SWITCH',
        'MAP',
        'BINARY',
        'MD5',
        'UNIX_TIMESTAMP',
        'TIMESTAMPDIFF_DAY',
        'TIMESTAMPDIFF_MONTH',
        'TIMESTAMPDIFF_YEAR',
        'TIMESTAMPDIFF_WEEK',
        'TIMESTAMPDIFF_HOUR',
        'TIMESTAMPDIFF_MINUTE',
        'TIMESTAMPDIFF_SECOND',
        'POSITION_IN_LIST',
        'MATCH_BOOLEAN',
        'MATCH_NATURAL_LANGUAGE',
        'ANY_VALUE',
    ];

    public const COMPARISON_FUNCTION_LIST = [
        'LIKE',
        'NOT_LIKE',
        'EQUAL',
        'NOT_EQUAL',
        'GREATER_THAN',
        'LESS_THAN',
        'GREATER_THAN_OR_EQUAL',
        'LESS_THAN_OR_EQUAL',
    ];

    public const MATH_OPERATION_FUNCTION_LIST = [
        'ADD',
        'SUB',
        'MUL',
        'DIV',
        'MOD',
    ];
}
