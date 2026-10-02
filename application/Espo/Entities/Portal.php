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

namespace Espo\Entities;

use Espo\Core\Field\Link;
use Espo\Core\ORM\Entity;
use Espo\Repositories\Portal as PortalRepository;

class Portal extends Entity
{
    public const ENTITY_TYPE = 'Portal';

    /**
     * @var string[]
     */
    protected $settingsAttributeList = [
        'applicationName',
        'companyLogoId',
        'tabList',
        'quickCreateList',
        'dashboardLayout',
        'dashletsOptions',
        'theme',
        'themeParams',
        'language',
        'timeZone',
        'dateFormat',
        'timeFormat',
        'weekStart',
        'defaultCurrency',
    ];

    /**
     * @return string[]
     */
    public function getSettingsAttributeList(): array
    {
        return $this->settingsAttributeList;
    }

    public function getUrl(): ?string
    {
        if (!$this->has('url') && $this->entityManager) {
            /** @var PortalRepository $repository */
            $repository = $this->entityManager->getRDBRepositoryByClass(Portal::class);

            $repository->loadUrlField($this);
        }

        return $this->get('url');
    }

    public function getAuthenticationProvider(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('authenticationProvider');
    }

    public function getLayoutSet(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('layoutSet');
    }

    public function isActive(): bool
    {
        return $this->get('isActive');
    }
}
