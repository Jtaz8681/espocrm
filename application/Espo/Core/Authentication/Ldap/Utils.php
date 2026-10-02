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

namespace Espo\Core\Authentication\Ldap;

use Espo\Core\Utils\Config;

class Utils
{
    private Config $config;

    /**
     * @var ?array<string, mixed>
     */
    private ?array $options = null;

    /**
     * @var array<string, string>
     */
    private $fieldMap = [
        'host' => 'ldapHost',
        'port' => 'ldapPort',
        'useSsl' => 'ldapSecurity',
        'useStartTls' => 'ldapSecurity',
        'username' => 'ldapUsername',
        'password' => 'ldapPassword',
        'bindRequiresDn' => 'ldapBindRequiresDn',
        'baseDn' => 'ldapBaseDn',
        'accountCanonicalForm' => 'ldapAccountCanonicalForm',
        'accountDomainName' => 'ldapAccountDomainName',
        'accountDomainNameShort' => 'ldapAccountDomainNameShort',
        'accountFilterFormat' => 'ldapAccountFilterFormat',
        'optReferrals' => 'ldapOptReferrals',
        'tryUsernameSplit' => 'ldapTryUsernameSplit',
        'networkTimeout' => 'ldapNetworkTimeout',
        'createEspoUser' => 'ldapCreateEspoUser',
        'userNameAttribute' => 'ldapUserNameAttribute',
        'userTitleAttribute' => 'ldapUserTitleAttribute',
        'userFirstNameAttribute' => 'ldapUserFirstNameAttribute',
        'userLastNameAttribute' => 'ldapUserLastNameAttribute',
        'userEmailAddressAttribute' => 'ldapUserEmailAddressAttribute',
        'userPhoneNumberAttribute' => 'ldapUserPhoneNumberAttribute',
        'userLoginFilter' => 'ldapUserLoginFilter',
        'userTeamsIds' => 'ldapUserTeamsIds',
        'userDefaultTeamId' => 'ldapUserDefaultTeamId',
        'userObjectClass' => 'ldapUserObjectClass',
        'portalUserLdapAuth' => 'ldapPortalUserLdapAuth',
        'portalUserPortalsIds' => 'ldapPortalUserPortalsIds',
        'portalUserRolesIds' => 'ldapPortalUserRolesIds',
    ];

    /**
     * @var array<int, string>
     */
    private $permittedEspoOptions = [
        'createEspoUser',
        'userNameAttribute',
        'userObjectClass',
        'userTitleAttribute',
        'userFirstNameAttribute',
        'userLastNameAttribute',
        'userEmailAddressAttribute',
        'userPhoneNumberAttribute',
        'userLoginFilter',
        'userTeamsIds',
        'userDefaultTeamId',
        'portalUserLdapAuth',
        'portalUserPortalsIds',
        'portalUserRolesIds',
    ];

    /**
     * AccountCanonicalForm Map between Espo and Laminas value.
     *
     *  @var array<string, int>
     */
    private $accountCanonicalFormMap = [
        'Dn' => 1,
        'Username' => 2,
        'Backslash' => 3,
        'Principal' => 4,
    ];

    public function __construct(?Config $config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        }
    }

    /**
     * Get Options from espo config according to $this->fieldMap.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        if (isset($this->options)) {
            return $this->options;
        }

        $options = [];

        foreach ($this->fieldMap as $ldapName => $espoName) {
            $option = $this->config->get($espoName);

            if (isset($option)) {
                $options[$ldapName] = $option;
            }
        }

        $this->options = $this->normalizeOptions($options);

        return $this->options;
    }

    /**
     * Normalize options to LDAP client format
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function normalizeOptions(array $options): array
    {
        $useSsl = ($options['useSsl'] ?? null) == 'SSL';
        $useStartTls = ($options['useStartTls'] ?? null) == 'TLS';
        $accountCanonicalFormKey = $options['accountCanonicalForm'] ?? 'Dn';

        $options['useSsl'] = $useSsl;
        $options['useStartTls'] = $useStartTls;
        $options['accountCanonicalForm'] = $this->accountCanonicalFormMap[$accountCanonicalFormKey] ?? 1;

        return $options;
    }

    /**
     * Get an LDAP option.
     *
     * @param string $name
     * @param mixed $returns A default value.
     * @return mixed
     */
    public function getOption($name, $returns = null)
    {
        if (!isset($this->options)) {
            $this->getOptions();
        }

        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $returns;
    }

    /**
     * Get Laminas options for using Laminas\Ldap.
     *
     * @return array<string, mixed>
     */
    public function getLdapClientOptions(): array
    {
        $options = $this->getOptions();

        return array_diff_key($options, array_flip($this->permittedEspoOptions));
    }
}
