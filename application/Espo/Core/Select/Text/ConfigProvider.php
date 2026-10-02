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

namespace Espo\Core\Select\Text;

use Espo\Core\Utils\Config;
use libphonenumber\PhoneNumberUtil;

class ConfigProvider
{
    private const MIN_LENGTH_FOR_CONTENT_SEARCH = 4;

    public function __construct(private Config $config)
    {}

    /**
     * Full-text search min indexed word length.
     *
     * @internal Do not use.
     * @since 8.3.0
     */
    public function getFullTextSearchMinLength(): ?int
    {
        return $this->config->get('fullTextSearchMinLength');
    }

    public function getMinLengthForContentSearch(): int
    {
        return $this->config->get('textFilterContainsMinLength') ??
            self::MIN_LENGTH_FOR_CONTENT_SEARCH;
    }

    public function useContainsForVarchar(): bool
    {
        return $this->config->get('textFilterUseContainsForVarchar') ?? false;
    }

    public function usePhoneNumberNumericSearch(): bool
    {
        return $this->config->get('phoneNumberNumericSearch') ?? false;
    }

    public function isPhoneNumberInternational(): bool
    {
        return $this->config->get('phoneNumberInternational') ?? false;
    }

    /**
     * @return int[]
     * @since 10.0.8
     */
    public function getPreferredPhoneNumberCountryCodes(): array
    {
        $regionCodes = $this->config->get('phoneNumberPreferredCountryList') ?? [];

        $codes = array_map(function ($code) {
            return PhoneNumberUtil::getInstance()->getCountryCodeForRegion($code);
        }, $regionCodes);

        $codes = array_filter($codes, fn ($it) => $it !== 0);

        return array_values($codes);
    }
}
