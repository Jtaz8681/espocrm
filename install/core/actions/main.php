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

$config = $installer->getConfig();

$fields = [
    'user-lang' => [
        'default' => $config->get('language', 'en_US'),
    ],
    'theme' => [
        'default' => $config->get('theme'),
    ],
];

foreach ($fields as $fieldName => $field) {
    if (isset($_SESSION['install'][$fieldName])) {
        $fields[$fieldName]['value'] = $_SESSION['install'][$fieldName];
    } else {
        $fields[$fieldName]['value'] = (isset($field['default']))? $field['default'] : '';
    }
}

$language = $installer->createLanguage($_SESSION['install']['user-lang'] ?? 'en_US');

$themes = [];
foreach ($installer->getThemeList() as $item) {
    $themes[$item] = $language->translate($item, 'themes', 'Global');
}

$smarty->assign('themeLabel', $language->translate('theme', 'fields', 'Settings'));
$smarty->assign('fields', $fields);
$smarty->assign("themes", $themes);
