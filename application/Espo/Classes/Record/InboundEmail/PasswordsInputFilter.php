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

namespace Espo\Classes\Record\InboundEmail;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\Input\Data;
use Espo\Core\Record\Input\Filter;
use Espo\Core\Utils\Crypt;

/**
 * @noinspection PhpUnused
 */
class PasswordsInputFilter implements Filter
{
    public function __construct(
        private Crypt $crypt
    ) {}

    /**
     * @throws BadRequest
     */
    public function filter(Data $data): void
    {
        $password = $data->get('password');

        if ($password !== null) {
            if (!is_string($password)) {
                throw new BadRequest();
            }

            $data->set('password', $this->crypt->encrypt($password));
        }

        $smtpPassword = $data->get('smtpPassword');

        if ($smtpPassword !== null) {
            if (!is_string($smtpPassword)) {
                throw new BadRequest();
            }

            $data->set('smtpPassword', $this->crypt->encrypt($smtpPassword));
        }
    }
}
