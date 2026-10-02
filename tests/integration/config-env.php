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

return [
    'database' => [
        'platform' => getenv('TEST_DATABASE_PLATFORM') ?: 'Mysql',
        'charset' => getenv('TEST_DATABASE_CHARSET') ?: 'utf8mb4',
        'host' => getenv('TEST_DATABASE_HOST'),
        'port' => getenv('TEST_DATABASE_PORT'),
        'dbname' => getenv('TEST_DATABASE_NAME'),
        'user' => getenv('TEST_DATABASE_USER'),
        'password' => getenv('TEST_DATABASE_PASSWORD'),
    ],
];
