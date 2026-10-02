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

namespace Espo\Hooks\Portal;

use Espo\ORM\Entity;
use Espo\Entities\Portal;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;

class WriteConfig
{
    public function __construct(private Config $config, private ConfigWriter $configWriter)
    {}

    /**
     * @param Portal $entity
     */
    public function afterSave(Entity $entity): void
    {
        if (!$entity->has('isDefault')) {
            return;
        }

        if ($entity->get('isDefault')) {
            $defaultPortalId = $this->config->get('defaultPortalId');

            if ($defaultPortalId === $entity->getId()) {
                return;
            }

            $this->configWriter->set('defaultPortalId', $entity->getId());

            $this->configWriter->save();
        }

        if ($entity->isAttributeChanged('isDefault') && $entity->getFetched('isDefault')) {
            $this->configWriter->set('defaultPortalId', null);

            $this->configWriter->save();
        }
    }
}
