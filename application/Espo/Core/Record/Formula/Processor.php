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

namespace Espo\Core\Record\Formula;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Formula\Exceptions\Error as FormulaError;
use Espo\Core\Formula\Exceptions\WrapperException;
use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\UpdateParams;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use RuntimeException;
use stdClass;

/**
 * Formula script processing for API requests.
 */
class Processor
{
    public function __construct(
        private FormulaManager $formulaManager,
        private Metadata $metadata,
    ) {}

    /**
     * Process a before-create formula script.
     *
     * @throws BadRequest
     * @throws Forbidden
     * @throws Conflict
     */
    public function processBeforeCreate(Entity $entity, CreateParams $params): void
    {
        $script = $this->getScript($entity->getEntityType());

        if (!$script) {
            return;
        }

        $variables = (object) [
            '__skipDuplicateCheck' => $params->skipDuplicateCheck(),
            '__isRecordService' => true,
        ];

        $this->run($script, $entity, $variables);
    }

    /**
     * Process a before-update formula script.
     *
     * @throws BadRequest
     * @throws Forbidden
     * @throws Conflict
     */
    public function processBeforeUpdate(Entity $entity, UpdateParams $params): void
    {
        $script = $this->getScript($entity->getEntityType());

        if (!$script) {
            return;
        }

        $variables = (object) [
            '__skipDuplicateCheck' => $params->skipDuplicateCheck(),
            '__isRecordService' => true,
        ];

        $this->run($script, $entity, $variables);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Conflict
     */
    private function run(string $script, Entity $entity, stdClass $variables): void
    {
        try {
            $this->formulaManager->run($script, $entity, $variables);
        } catch (WrapperException $e) {
            throw $e->getWrappedException();
        } catch (FormulaError $e) {
            throw new RuntimeException('Before save API script error.', previous: $e);
        }
    }

    private function getScript(string $entityType): ?string
    {
        /** @var ?string */
        return $this->metadata->get(['formula', $entityType, 'beforeSaveApiScript']);
    }
}
