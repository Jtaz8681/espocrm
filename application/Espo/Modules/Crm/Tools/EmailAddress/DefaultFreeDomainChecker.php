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

namespace Espo\Modules\Crm\Tools\EmailAddress;

use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Json;
use RuntimeException;

/**
 * @since 9.0.3
 */
class DefaultFreeDomainChecker implements FreeDomainChecker
{
    private string $file = 'application/Espo/Modules/Crm/Resources/data/freeEmailProviderDomains.json';

    public function __construct(
        private FileManager $fileManager
    ) {}

    public function check(string $domain): bool
    {
        $list = Json::decode($this->fileManager->getContents($this->file));

        if (!is_array($list)) {
            throw new RuntimeException("Bad data in freeEmailProviderDomains file.");
        }

        return in_array($domain, $list);
    }
}
