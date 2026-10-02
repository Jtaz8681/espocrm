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

namespace Espo\Core\ORM\Type;

class FieldType
{
    public const string VARCHAR = 'varchar';
    public const string BOOL = 'bool';
    public const string TEXT = 'text';
    public const string INT = 'int';
    public const string FLOAT = 'float';
    public const string DATE = 'date';
    public const string DATETIME = 'datetime';
    public const string DATETIME_OPTIONAL = 'datetimeOptional';
    public const string ENUM = 'enum';
    public const string MULTI_ENUM = 'multiEnum';
    public const string ARRAY = 'array';
    public const string CHECKLIST = 'checklist';
    public const string CURRENCY = 'currency';
    public const string CURRENCY_CONVERTED = 'currencyConverted';
    public const string PERSON_NAME = 'personName';
    public const string ADDRESS = 'address';
    public const string EMAIL = 'email';
    public const string PHONE = 'phone';
    public const string AUTOINCREMENT = 'autoincrement';
    public const string URL = 'url';
    public const string NUMBER = 'number';
    public const string LINK = 'link';
    public const string LINK_ONE = 'linkOne';
    public const string LINK_PARENT = 'linkParent';
    public const string FILE = 'file';
    public const string IMAGE = 'image';
    public const string LINK_MULTIPLE = 'linkMultiple';
    public const string ATTACHMENT_MULTIPLE = 'attachmentMultiple';
    public const string FOREIGN = 'foreign';
    public const string WYSIWYG = 'wysiwyg';
    public const string JSON_ARRAY = 'jsonArray';
    public const string JSON_OBJECT = 'jsonObject';
    public const string PASSWORD = 'password';

    /**
     * @since 9.3.0
     */
    public const string DECIMAL = 'decimal';

    /**
     * @since 9.3.0
     */
    public const string URL_MULTIPLE = 'urlMultiple';

    /**
     * @since 9.3.0
     */
    public const string BARCODE = 'barcode';
}
