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

namespace Espo\Hooks\Common;

use Espo\Core\Formula\Exceptions\ValidationFunctionException;
use Espo\ORM\Entity;
use Espo\ORM\Exceptions\PersistenceException;
use Espo\ORM\Exceptions\ValidationException;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Core\Utils\Metadata;

use Exception;
use stdClass;

/**
 * @implements BeforeSave<Entity>
 */
class Formula implements BeforeSave
{
    public static int $order = 11;

    public function __construct(
        private Metadata $metadata,
        private FormulaManager $formulaManager,
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($options->get('skipFormula')) {
            return;
        }

        $scriptList = $this->metadata->get(['formula', $entity->getEntityType(), 'beforeSaveScriptList'], []);

        $variables = (object) [];

        foreach ($scriptList as $script) {
            $this->runScript($script, $entity, $variables);
        }

        $customScript = $this->metadata->get(['formula', $entity->getEntityType(), 'beforeSaveCustomScript']);

        if (!$customScript) {
            return;
        }

        $this->runScript($customScript, $entity, $variables);
    }

    private function runScript(string $script, Entity $entity, stdClass $variables): void
    {
        try {
            $this->formulaManager->run($script, $entity, $variables);
        } catch (Exception $e) {
            if ($e instanceof ValidationFunctionException) {
                throw new ValidationException("Before-save validation error.", previous: $e);
            }

            throw new PersistenceException("Before-save formula script failed.", previous: $e);
        }
    }
}
