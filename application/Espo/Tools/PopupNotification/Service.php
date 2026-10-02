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

namespace Espo\Tools\PopupNotification;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Throwable;

class Service
{
    public function __construct(
        private Metadata $metadata,
        private User $user,
        private Log $log,
        private InjectableFactory $injectableFactory,
    ) {}

    /**
     * @return array<string, Item[]> Items grouped by type.
     */
    public function getGrouped(): array
    {
        $data = $this->metadata->get(['app', 'popupNotifications']) ?? [];

        $data = array_filter($data, function ($item) {
            if (!($item['grouped'] ?? false)) {
                return false;
            }

            if ($item['disabled'] ?? false) {
                return false;
            }

            if (empty($item['providerClassName'])) {
                return false;
            }

            $portalDisabled = $item['portalDisabled'] ?? false;

            if ($portalDisabled && $this->user->isPortal()) {
                return false;
            }

            return true;
        });

        $result = [];

        foreach ($data as $type => $item) {
            /** @var ?class-string<Provider> $className */
            $className = $item['providerClassName'] ?? null;

            if (!$className) {
                continue;
            }

            try {
                $provider = $this->injectableFactory->create($className);

                $result[$type] = $provider->get($this->user);
            } catch (Throwable $e) {
                $this->log->error("Popup notification error.", ['exception' => $e]);
            }
        }

        return $result;
    }
}
