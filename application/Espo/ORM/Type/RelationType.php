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

namespace Espo\ORM\Type;

class RelationType
{
    public const MANY_MANY = 'manyMany';
    public const HAS_MANY = 'hasMany';
    public const BELONGS_TO = 'belongsTo';
    public const HAS_ONE = 'hasOne';
    public const BELONGS_TO_PARENT = 'belongsToParent';
    public const HAS_CHILDREN = 'hasChildren';
}
