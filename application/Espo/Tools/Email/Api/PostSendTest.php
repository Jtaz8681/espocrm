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

namespace Espo\Tools\Email\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Mail\Account\Util\AddressUtil;
use Espo\Core\Mail\Exceptions\NoSmtp;
use Espo\Core\Mail\SmtpParams;
use Espo\Core\Utils\Security\HostCheck;
use Espo\Entities\Email;
use Espo\Tools\Email\SendService;
use Espo\Tools\Email\TestSendData;

/**
 * Sends test emails.
 */
class PostSendTest implements Action
{
    public function __construct(
        private SendService $sendService,
        private Acl $acl,
        private HostCheck $hostCheck,
        private AddressUtil $addressUtil,
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NoSmtp
     * @throws NotFound
     */
    public function process(Request $request): Response
    {
        if (!$this->acl->checkScope(Email::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $data = $request->getParsedBody();

        $type = $data->type ?? null;
        $id = $data->id ?? null;
        $server = $data->server ?? null;
        $port = $data->port ?? null;
        $username = $data->username ?? null;
        $password = $data->password ?? null;
        $auth = $data->auth ?? null;
        $authMechanism = $data->authMechanism ?? null;
        $security = $data->security ?? null;
        $userId = $data->userId ?? null;
        $fromAddress = $data->fromAddress ?? null;
        $fromName = $data->fromName ?? null;
        $emailAddress = $data->emailAddress ?? null;

        if (!is_string($server)) {
            throw new BadRequest("No `server`");
        }

        if (!is_int($port)) {
            throw new BadRequest("No or bad `port`.");
        }

        if (!is_string($emailAddress)) {
            throw new BadRequest("No `emailAddress`.");
        }

        $smtpParams = SmtpParams
            ::create($server, $port)
            ->withSecurity($security)
            ->withFromName($fromName)
            ->withFromAddress($fromAddress)
            ->withAuth($auth);

        if ($auth) {
            $smtpParams = $smtpParams
                ->withUsername($username)
                ->withPassword($password)
                ->withAuthMechanism($authMechanism);
        }

        if (
            !$this->addressUtil->isAllowedAddress($smtpParams) &&
            !$this->hostCheck->isHostAndNotInternal($server)
        ) {
            throw new Forbidden("Not allowed internal host.");
        }

        $data = new TestSendData($emailAddress, $type, $id, $userId);

        $this->sendService->sendTestEmail($smtpParams, $data);

        return ResponseComposer::json(true);
    }
}
