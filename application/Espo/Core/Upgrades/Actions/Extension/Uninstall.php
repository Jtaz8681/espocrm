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
use Throwable;

class Uninstall extends \Espo\Core\Upgrades\Actions\Base\Uninstall
{
    protected ?Extension $extensionEntity = null;

    /**
     * Get entity of this extension.
     *
     * @return Extension
     * @throws Error
     */
    protected function getExtensionEntity()
    {
        if (!isset($this->extensionEntity)) {
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
        /** Set extension entity, isInstalled = false */
        $extensionEntity = $this->getExtensionEntity();
        $extensionEntity->set('isInstalled', false);

        try {
            $this->getEntityManager()->saveEntity($extensionEntity);
        } catch (Throwable $e) {
            $this->getLog()->error(
                'Error saving Extension entity. The error occurred by existing Hook, more details: ' .
                $e->getMessage() .' at '. $e->getFile() . ':' . $e->getLine()
            );

            $this->throwErrorAndRemovePackage('Error saving Extension entity. Check logs for details.', false);
        }
    }

    /**
     * @return string[]
     * @throws Error
     */
    protected function getRestoreFileList(): array
    {
        if (!isset($this->data['restoreFileList'])) {
            $extensionEntity = $this->getExtensionEntity();
            $this->data['restoreFileList'] = $extensionEntity->get('fileList');
        }

        return $this->data['restoreFileList'] ?? [];
    }
}
