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

if (substr(php_sapi_name(), 0, 3) != 'cli') {
    die('The file can be run only via CLI.');
}

$options = getopt("a:d:");

if (empty($options['a'])) {
    fwrite(\STDOUT, "Error: the option [-a] is required.\n");

    exit;
}

$allPostData = [];

if (!empty($options['d'])) {
    parse_str($options['d'], $allPostData);

    if (empty($allPostData) || !is_array($allPostData)) {
        fwrite(\STDOUT, "Error: Incorrect input data.\n");

        exit;
    }
}

$action = $options['a'];
$allPostData['action'] = $action;

chdir(dirname(__FILE__));
set_include_path(dirname(__FILE__));

require_once('../bootstrap.php');

$_SERVER['SERVER_SOFTWARE'] = 'Cli';

require_once('core/PostData.php');

$postData = new PostData();
$postData->set($allPostData);

require_once('core/InstallerConfig.php');

$installerConfig = new InstallerConfig();

if ($installerConfig->get('isInstalled')) {
    fwrite(\STDOUT, "Error: EspoCRM is already installed.\n");

    exit;
}

if (session_status() != \PHP_SESSION_ACTIVE) {
    if (!$installerConfig->get('cliSessionId')) {
        session_start();

        $installerConfig->set('cliSessionId', session_id());
        $installerConfig->save();
    } else {
        session_id($installerConfig->get('cliSessionId'));
    }
}

ob_start();

try {
    require('entry.php');
} catch (\Throwable $e) {
    fwrite(\STDOUT, "Error: ". $e->getMessage() .".\n");

    exit;
}

$result = ob_get_contents();
ob_end_clean();

if (preg_match('/"success":false/i', $result)) {
    $resultData = json_decode($result, true);

    if (empty($resultData)) {
        fwrite(\STDOUT, "Error: Unexpected error occurred.\n");

        exit;
    }

    fwrite(
        \STDOUT,
        "Error: ". (!empty($resultData['errors']) ?
            print_r($resultData['errors'], true) :
            $resultData['errorMsg']) ."\n"
    );

    exit;
}
