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

namespace Espo\Modules\Crm\Tools\MassEmail;

use Espo\Core\Utils\Config;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\Modules\Crm\Tools\MassEmail\MessagePreparator\Data;
use Espo\Modules\Crm\Tools\MassEmail\MessagePreparator\Headers;

class DefaultMessageHeadersPreparator implements MessageHeadersPreparator
{
    public function __construct(
        private Config $config,
        private Config\ApplicationConfig $applicationConfig,
    ) {}

    public function prepare(Headers $headers, Data $data): void
    {
        $headers->addTextHeader('X-Queue-Item-Id', $data->getId());
        $headers->addTextHeader('Precedence', 'bulk');

        $campaignType = $this->getCampaignType($data);

        if (
            $campaignType === Campaign::TYPE_INFORMATIONAL_EMAIL ||
            $campaignType === Campaign::TYPE_NEWSLETTER
        ) {
            $headers->addTextHeader('Auto-Submitted', 'auto-generated');
            $headers->addTextHeader('X-Auto-Response-Suppress', 'AutoReply');
        }

        $this->addMandatoryOptOut($headers, $data);
    }

    private function getSiteUrl(): string
    {
        $url = $this->config->get('massEmailSiteUrl') ?? $this->applicationConfig->getSiteUrl();

        return rtrim($url, '/');
    }

    private function addMandatoryOptOut(Headers $headers, Data $data): void
    {
        if ($this->getCampaignType($data) === Campaign::TYPE_INFORMATIONAL_EMAIL) {
            return;
        }

        if ($this->config->get('massEmailDisableMandatoryOptOutLink')) {
            return;
        }

        $id = $data->getId();

        $url = "{$this->getSiteUrl()}/api/v1/Campaign/unsubscribe/$id";

        $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $headers->addTextHeader('List-Unsubscribe', "<$url>");
    }

    private function getCampaignType(Data $data): ?string
    {
        return $data->getQueueItem()->getMassEmail()?->getCampaign()?->getType();
    }
}
