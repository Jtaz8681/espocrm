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
$result = ['success' => false, 'errorMsg' => ''];

// create user
if (!empty($_SESSION['install']['user-name']) && !empty($_SESSION['install']['user-pass'])) {
    $userId = $installer->createUser($_SESSION['install']['user-name'], $_SESSION['install']['user-pass']);

    if (!empty($userId)) {
        $result['success'] = true;
    } else {
        $result['success'] = false;
        $result['errorMsg'] = 'Cannot create user';
    }
} else {
    $result['success'] = false;
    $result['errorMsg'] = 'Cannot create user';
}

ob_clean();
echo json_encode($result);
