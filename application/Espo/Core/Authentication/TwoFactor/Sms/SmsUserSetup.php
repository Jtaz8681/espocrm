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

namespace Espo\Core\Authentication\TwoFactor\Sms;

use Espo\Core\Authentication\TwoFactor\Exceptions\NotConfigured;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Portal\Utils\Config;
use Espo\Entities\User;
use Espo\Core\Authentication\TwoFactor\UserSetup;

use stdClass;

/**
 * @noinspection PhpUnused
 */
class SmsUserSetup implements UserSetup
{
    public function __construct(
        private Util $util,
        private Config $config
    ) {}

    public function getData(User $user): stdClass
    {
        if (!$this->config->get('smsProvider')) {
            throw new NotConfigured("No SMS provider.");
        }

        return (object) [
            'phoneNumberList' => $user->getPhoneNumberGroup()->getNumberList(),
        ];
    }

    public function verifyData(User $user, stdClass $payloadData): bool
    {
        $code = $payloadData->code ?? null;

        if ($code === null) {
            throw new BadRequest("No code.");
        }

        $codeModified = str_replace(' ', '', trim($code));

        if (!$codeModified) {
            return false;
        }

        return $this->util->verifyCode($user, $codeModified);
    }
}
