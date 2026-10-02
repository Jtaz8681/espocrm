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

class AttributeType
{
    public const ID = 'id';
    public const VARCHAR = 'varchar';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const TEXT = 'text';
    public const BOOL = 'bool';
    public const FOREIGN_ID = 'foreignId';
    public const FOREIGN = 'foreign';
    public const FOREIGN_TYPE = 'foreignType';
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    public const JSON_ARRAY = 'jsonArray';
    public const JSON_OBJECT = 'jsonObject';
    public const PASSWORD = 'password';
}
