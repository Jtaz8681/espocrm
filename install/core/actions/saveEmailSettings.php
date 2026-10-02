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

if (!empty($_SESSION['install'])) {

    $paramList = [
        'outboundEmailFromName',
        'outboundEmailFromAddress',
        'outboundEmailIsShared',
    ];

    $preferences = [];

    foreach ($paramList as $paramName) {
        switch ($paramName) {
            case 'outboundEmailIsShared':
                $preferences['outboundEmailIsShared'] = $_SESSION['install']['outboundEmailIsShared'] === 'true';

                break;

            default:
                if (array_key_exists($paramName, $_SESSION['install'])) {
                    $preferences[$paramName] = $_SESSION['install'][$paramName];
                }

                break;
        }

    }

    $res = $installer->savePreferences($preferences);

    if (!empty($res)) {
        $result['success'] = true;
    } else {
        $result['success'] = false;
        $result['errorMsg'] = 'Cannot save preferences';
    }
} else {
    $result['success'] = false;
    $result['errorMsg'] = 'Cannot save preferences';
}

ob_clean();
echo json_encode($result);
