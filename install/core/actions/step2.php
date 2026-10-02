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

$clearedCookieList = [
    'auth-token-secret',
    'auth-username',
    'auth-token',
];

foreach ($clearedCookieList as $cookieName) {
    if (!isset($_COOKIE[$cookieName])) {
        continue;
    }

    setcookie($cookieName, null, -1, '/');
}

$config = $installer->getConfig();

$fields = [
    'db-platform' => [
        'default' => $config->get('database.platform', 'Mysql'),
    ],
    'db-driver' => [
        'default' => $config->get('database.driver', ''),
    ],
    'db-name' => [
        'default' => $config->get('database.dbname', ''),
    ],
    'host-name' => [
        'default' => $config->get('database.host', '') .
            ($config->get('database.port') ? ':' . $config->get('database.port') : ''),
    ],
    'db-user-name' => [
        'default' => $config->get('database.user', ''),
    ],
    'db-user-password' => [],
];

foreach ($fields as $fieldName => $field) {
    if (isset($_SESSION['install'][$fieldName])) {
        $fields[$fieldName]['value'] = $_SESSION['install'][$fieldName];
    } else {
        $fields[$fieldName]['value'] = $field['default'] ?? '';
    }
}

$platforms = [
    'Mysql' => 'MySQL / MariaDB',
    'Postgresql' => 'PostgreSQL',
];

$smarty->assign('platforms', $platforms);

$smarty->assign('fields', $fields);
