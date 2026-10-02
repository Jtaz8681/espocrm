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

ob_start();

$result = [
    'success' => true,
    'errorMsg' => '',
];

// save settings
$database = [
    'dbname' => $_SESSION['install']['db-name'],
    'user' => $_SESSION['install']['db-user-name'],
    'password' => $_SESSION['install']['db-user-password'],
    'platform' => $_SESSION['install']['db-platform'] ?? 'Mysql',
];

$host = $_SESSION['install']['host-name'];

if (!str_contains($host, ':')) {
    $host .= ":";
}

[$database['host'], $database['port']] = explode(':', $host);

$saveData = [
    'database' => $database,
    'language' => !empty($_SESSION['install']['user-lang']) ? $_SESSION['install']['user-lang'] : 'en_US',
    'siteUrl' => !empty($_SESSION['install']['site-url']) ? $_SESSION['install']['site-url'] : null,
];

if (!empty($_SESSION['install']['theme'])) {
    $saveData['theme'] = $_SESSION['install']['theme'];
}

if (!empty($_SESSION['install']['default-permissions-user']) && !empty($_SESSION['install']['default-permissions-group'])) {
    $saveData['defaultPermissions'] = [
        'user' => $_SESSION['install']['default-permissions-user'],
        'group' => $_SESSION['install']['default-permissions-group'],
    ];
}

if (!$installer->saveData($saveData)) {
    $result['success'] = false;
    $result['errorMsg'] = $langs['messages']['Can not save settings'];
}

ob_clean();
echo json_encode($result);
