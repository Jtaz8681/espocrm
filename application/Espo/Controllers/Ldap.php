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

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Authentication\Ldap\Utils as LDAPUtils;
use Espo\Core\Authentication\Ldap\Client as LDAPClient;
use Espo\Core\Api\Request;
use Espo\Core\Utils\Config;
use Espo\Entities\User;

use Laminas\Ldap\Exception\LdapException;


class Ldap
{
    private User $user;
    private Config $config;

    public function __construct(
        User $user,
        Config $config
    ) {
        $this->user = $user;
        $this->config = $config;
    }

    /**
     * @throws Forbidden
     * @throws LdapException
     */
    public function postActionTestConnection(Request $request): bool
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $data = $request->getParsedBody();

        if (!isset($data->password)) {
            $data->password = $this->config->get('ldapPassword');
        }

        $ldapUtils = new LDAPUtils();

        $options = $ldapUtils->normalizeOptions(
            get_object_vars($data)
        );

        $ldapClient = new LDAPClient($options);

        // An exception thrown if no connection.
        $ldapClient->bind();

        return true;
    }
}
