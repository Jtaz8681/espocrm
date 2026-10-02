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
    'errors' => [],
];

$allPostData = $postData->getAll();

$platform = $allPostData['dbPlatform'] ?? 'Mysql';

$phpRequiredList = $installer->getSystemRequirementList('php', true, [
    'databaseParams' => [
        'platform' => $platform,
    ]
]);

foreach ($phpRequiredList as $name => $details) {
    if (!$details['acceptable']) {

        switch ($details['type']) {
            case 'version':
                $result['success'] = false;
                $result['errors']['phpVersion'] = $details['required'];

                break;

            default:
                $result['success'] = false;
                $result['errors']['phpRequires'][] = $name;

                break;
        }
    }
}



if (
    $result['success'] &&
    !empty($allPostData['dbName']) &&
    !empty($allPostData['hostName']) &&
    !empty($allPostData['dbUserName'])
) {
    $connect = false;

    $dbName = trim($allPostData['dbName']);

    if (!str_contains($allPostData['hostName'], ':')) {
        $allPostData['hostName'] .= ":";
    }

    [$hostName, $port] = explode(':', trim($allPostData['hostName']));

    $dbUserName = trim($allPostData['dbUserName']);
    $dbUserPass = trim($allPostData['dbUserPass']);

    if (!$port) {
        $port = null;
    }

    $databaseParams = [
        'platform' => $platform,
        'host' => $hostName,
        'port' => $port,
        'user' => $dbUserName,
        'password' => $dbUserPass,
        'dbname' => $dbName,
    ];

    $isConnected = true;

    try {
        $installer->checkDatabaseConnection($databaseParams, true);
    } catch (\Exception $e) {
        $isConnected = false;
        $result['success'] = false;
        $result['errors']['dbConnect']['errorCode'] = $e->getCode();
        $result['errors']['dbConnect']['errorMsg'] = $e->getMessage();
    }

    if ($isConnected) {
        $databaseRequiredList = $installer
            ->getSystemRequirementList('database', true, ['databaseParams' => $databaseParams]);

        foreach ($databaseRequiredList as $name => $details) {
            if (!$details['acceptable']) {
                switch ($details['type']) {
                    case 'version':
                        $result['success'] = false;
                        $result['errors'][$name] = $details['required'];

                        break;

                    default:
                        $result['success'] = false;
                        $result['errors'][$name][] = $name;

                        break;
                }
            }
        }
    }

}

ob_clean();
echo json_encode($result);
