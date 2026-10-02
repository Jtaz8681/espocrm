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

namespace Espo\Controllers;

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Mail\Account\PersonalAccount\Service;
use Espo\Core\Mail\Account\Storage\Params as StorageParams;

use Espo\Core\Controllers\Record;
use Espo\Core\Api\Request;

class EmailAccount extends Record
{
    protected function checkAccess(): bool
    {
        return $this->acl->check('EmailAccountScope');
    }

    /**
     * @return string[]
     * @throws Forbidden
     * @throws Error
     */
    public function postActionGetFolders(Request $request): array
    {
        $data = $request->getParsedBody();

        $params = StorageParams::createBuilder()
            ->setHost($data->host ?? null)
            ->setPort($data->port ?? null)
            ->setSecurity($data->security ?? null)
            ->setUsername($data->username ?? null)
            ->setPassword($data->password ?? null)
            ->setId($data->id ?? null)
            ->setEmailAddress($data->emailAddress ?? null)
            ->setUserId($data->userId ?? null)
            ->build();

        return $this->getEmailAccountService()->getFolderList($params);
    }

    /**
     * @throws Error
     * @throws Forbidden
     */
    public function postActionTestConnection(Request $request): bool
    {
        $data = $request->getParsedBody();

        $params = StorageParams::createBuilder()
            ->setHost($data->host ?? null)
            ->setPort($data->port ?? null)
            ->setSecurity($data->security ?? null)
            ->setUsername($data->username ?? null)
            ->setPassword($data->password ?? null)
            ->setId($data->id ?? null)
            ->setEmailAddress($data->emailAddress ?? null)
            ->setUserId($data->userId ?? null)
            ->build();

        $this->getEmailAccountService()->testConnection($params);

        return true;
    }

    private function getEmailAccountService(): Service
    {
        /** @var Service */
        return $this->injectableFactory->create(Service::class);
    }
}
