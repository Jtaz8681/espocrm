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

$result = ['success' => true, 'errorMsg' => ''];

if (!$installer->checkPermission()) {
    $result['success'] = false;
    $error = $installer->getLastPermissionError();

    $urls = array_keys($error);

    $group = [];

    foreach ($error as $folder => $permission) {
        $group[implode('-', $permission)][] = $folder;
    }

    ksort($group);

    $instruction = '';
    $instructionSU = '';
    $changeOwner = true;

    foreach($group as $permission => $folders) {
        if ($permission == '0644-0755') {
            $folders = '';
        }

        $instruction .= $systemHelper
            ->getPermissionCommands([$folders, ''], explode('-', $permission), false, null, $changeOwner) . "<br>";

        $instructionSU .= $systemHelper
            ->getPermissionCommands([$folders, ''], explode('-', $permission), true, null, $changeOwner) . "<br>";

        if ($changeOwner) {
            $changeOwner = false;
        }
    }

    $result['errorMsg'] = $langs['messages']['Permission denied to'] . ':<br><pre>'.implode('<br>', $urls).'</pre>';

    $result['errorFixInstruction'] =
        str_replace( '"{C}"' , $instruction, $langs['messages']['permissionInstruction']) .
        "<br>" . str_replace( '{CSU}' , $instructionSU, $langs['messages']['operationNotPermitted']);
}

ob_clean();
echo json_encode($result);
