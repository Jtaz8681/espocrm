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

namespace Espo\Core\Mail\Account\GroupAccount;

use Espo\Core\Mail\Account\CommonStorageFactory;
use Espo\Core\Mail\Account\Storage;
use Espo\Core\Mail\Account\Storage\Params;
use Espo\Core\Mail\Account\Account;
use Espo\Core\Mail\Account\StorageFactory as StorageFactoryInterface;
use Espo\Core\Mail\Exceptions\NoImap;

class StorageFactory implements StorageFactoryInterface
{
    public function __construct(
        private CommonStorageFactory $commonStorageFactory,
    ) {}

    public function create(Account $account): Storage
    {
        $imapParams = $account->getImapParams();

        if (!$imapParams) {
            throw new NoImap("No IMAP params.");
        }

        $params = Params::createBuilder()
            ->setHost($imapParams->getHost())
            ->setPort($imapParams->getPort())
            ->setSecurity($imapParams->getSecurity())
            ->setUsername($imapParams->getUsername())
            ->setPassword($imapParams->getPassword())
            ->setId($account->getId())
            ->setImapHandlerClassName($account->getImapHandlerClassName())
            ->build();

        return $this->createWithParams($params);
    }

    public function createWithParams(Params $params): Storage
    {
        return $this->commonStorageFactory->create($params);
    }
}
