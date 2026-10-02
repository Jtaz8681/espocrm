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

namespace Espo\Modules\Crm\Entities;

use Espo\Core\ORM\Entity;
use LogicException;

class CampaignTrackingUrl extends Entity
{
    public const ENTITY_TYPE = 'CampaignTrackingUrl';

    public const ACTION_SHOW_MESSAGE = 'Show Message';

    public function get(string $attribute): mixed
    {
        if ($attribute === 'urlToUse') {
            return $this->getUrlToUseInternal();
        }

        return parent::get($attribute);
    }

    public function has(string $attribute): bool
    {
        if ($attribute === 'urlToUse') {
            return $this->hasUrlToUseInternal();
        }

        return parent::has($attribute);
    }

    public function getCampaignId(): ?string
    {
        return $this->get('campaignId');
    }

    public function getAction(): ?string
    {
        return $this->get('action');
    }

    public function getMessage(): ?string
    {
        return $this->get('message');
    }

    public function getUrl(): ?string
    {
        return $this->get('url');
    }

    public function getUrlToUse(): string
    {
        if (!$this->id) {
            throw new LogicException();
        }

        return $this->get('urlToUse');
    }

    private function getUrlToUseInternal(): string
    {
        return "{trackingUrl:$this->id}";
    }

    private function hasUrlToUseInternal(): bool
    {
        return !$this->isNew();
    }

    public function getCampaign(): ?Campaign
    {
        /** @var ?Campaign */
        return $this->relations->getOne('campaign');
    }
}
