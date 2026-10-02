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

$phpRequirementList = $installer->getSystemRequirementList('php');
$smarty->assign('phpRequirementList', $phpRequirementList);

$installData = $_SESSION['install'];
$hostData = explode(':', $installData['host-name']);

$dbConfig = [
    'host' => $hostData[0] ?? '',
    'port' => $hostData[1] ?? '',
    'dbname' => $installData['db-name'],
    'user' => $installData['db-user-name'],
    'password' => $installData['db-user-password'],
    'platform' => $installData['db-platform'] ?? null,
];

$mysqlRequirementList = $installer->getSystemRequirementList('database', false, ['databaseParams' => $dbConfig]);
$smarty->assign('mysqlRequirementList', $mysqlRequirementList);

$permissionRequirementList = $installer->getSystemRequirementList('permission');
$smarty->assign('permissionRequirementList', $permissionRequirementList);
