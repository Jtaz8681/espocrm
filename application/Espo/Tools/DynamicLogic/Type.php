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

namespace Espo\Tools\DynamicLogic;

enum Type: string
{
    case And = 'and';
    case Or = 'or';
    case Not = 'not';
    case Equals = 'equals';
    case NotEquals = 'notEquals';
    case IsEmpty = 'isEmpty';
    case IsNotEmpty = 'isNotEmpty';
    case IsTrue = 'isTrue';
    case IsFalse = 'isFalse';
    case Contains = 'contains';
    case Has = 'has';
    case NotContains = 'notContains';
    case NotHas = 'notHas';
    case StartsWith = 'startsWith';
    case EndsWith = 'endsWith';
    case Matches = 'matches';
    case GreaterThan = 'greaterThan';
    case LessThan = 'lessThan';
    case GreaterThanOrEquals = 'greaterThanOrEquals';
    case LessThanOrEquals = 'lessThanOrEquals';
    case In = 'in';
    case NotIn = 'notIn';
    case IsToday = 'isToday';
    case InFuture = 'inFuture';
    case InPast = 'inPast';
}
