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

namespace Espo\Core\Upgrades\Actions\Extension;
use Espo\Core\Exceptions\Error;
use Espo\Entities\Extension;

class Delete extends \Espo\Core\Upgrades\Actions\Base\Delete
{
    protected ?Extension $extensionEntity = null;

    /**
     * Get an entity of this extension.
     *
     * @throws Error
     */
    protected function getExtensionEntity(): Extension
    {
        if (!$this->extensionEntity) {
            $processId = $this->getProcessId();

            $this->extensionEntity = $this->getEntityManager()->getEntityById(Extension::ENTITY_TYPE, $processId);

            if (!$this->extensionEntity) {
                throw new Error('Extension entity not found.');
            }
        }

        return $this->extensionEntity;
    }

    /**
     * @throws Error
     */
    protected function afterRunAction(): void
    {
        /** Delete extension entity */
        $extensionEntity = $this->getExtensionEntity();

        $this->getEntityManager()->removeEntity($extensionEntity);
    }
}
