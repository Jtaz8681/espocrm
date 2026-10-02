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

$fields = array(
    'license-agree' => array(
        'default' => '0',
    ),
);

foreach ($fields as $fieldName => $field) {
    if (isset($_SESSION['install'][$fieldName])) {
        $fields[$fieldName]['value'] = $_SESSION['install'][$fieldName];
    } else {
        $fields[$fieldName]['value'] = (isset($fields[$fieldName]['default']))? $fields[$fieldName]['default'] : '';
    }
}

$smarty->assign('fields', $fields);

if (file_exists("LICENSE.txt")) {
    $license = file_get_contents('LICENSE.txt');
    $smarty->assign('license', $license);
}
