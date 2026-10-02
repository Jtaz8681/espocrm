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

/**
 * Creates the config file if it does not exist. Sets the 'version' from the `package.json` in the config.
 */

include "bootstrap.php";

use Espo\Core\Application;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Config\ConfigWriterFileManager;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Json;

if (!str_starts_with(php_sapi_name(), 'cli')) {
    return;
}

$fileManager = new FileManager();

$packageData = Json::decode(
    $fileManager->getContents('package.json')
);

$version = $packageData->version ?? null;

if (!$version) {
    return;
}

$configPath = 'data/config.php';

$configWriterFileManager = new ConfigWriterFileManager();

if (!$configWriterFileManager->isFile($configPath)) {
    $configWriterFileManager->putPhpContents($configPath, []);
}

$app = new Application();

$injectableFactory = $app->getContainer()->getByClass(InjectableFactory::class);
$config = $app->getContainer()->getByClass(Config::class);

if ($config->get('version') === $version) {
    return;
}

$configWriter = $injectableFactory->create(ConfigWriter::class);

$configWriter->set('version', $version);
$configWriter->save();


