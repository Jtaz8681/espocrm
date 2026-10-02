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

namespace Espo\Tools\LinkManager;

class Type
{
    public const MANY_TO_MANY = 'manyToMany';
    public const MANY_TO_ONE = 'manyToOne';
    public const ONE_TO_MANY = 'oneToMany';
    public const ONE_TO_ONE_LEFT = 'oneToOneLeft';
    public const ONE_TO_ONE_RIGHT = 'oneToOneRight';
    public const CHILDREN_TO_PARENT = 'childrenToParent';
}
