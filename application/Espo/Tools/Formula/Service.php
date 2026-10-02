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

namespace Espo\Tools\Formula;

use Espo\Core\Formula\Parser;
use Espo\Core\Formula\Exceptions\SyntaxError;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Manager;
use Espo\Core\Field\LinkParent;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\Utils\Log;
use Espo\ORM\EntityManager;
use Espo\ORM\Entity;

class Service
{
    public function __construct(
        private Parser $parser,
        private Manager $manager,
        private EntityManager $entityManager,
        private Log $log,
    ) {}

    public function checkSyntax(string $expression): SyntaxCheckResult
    {
        try {
            $this->parser->parse($expression);

            $result = SyntaxCheckResult::createSuccess();
        }  catch (SyntaxError $e) {
            return SyntaxCheckResult::createError($e);
        }

        return $result;
    }

    /**
     * @throws NotFoundSilent
     */
    public function run(string $expression, ?LinkParent $targetLink = null): RunResult
    {
        $syntaxCheckResult = $this->checkSyntax($expression);

        if (!$syntaxCheckResult->isSuccess()) {
            /** @var SyntaxError $exception */
            $exception = $syntaxCheckResult->getException();

            return RunResult::createSyntaxError($exception);
        }

        $target = null;

        if ($targetLink) {
            $target = $this->entityManager->getEntityById($targetLink->getEntityType(), $targetLink->getId());

            if (!$target) {
                throw new NotFoundSilent("Target entity not found.");
            }
        }

        $variables = (object) [];

        try {
            $this->manager->run($expression, $target, $variables);
        } catch (Error $e) {
            $output = $variables->__output ?? null;

            $this->log->error("Formula sandbox run error.", ['exception' => $e]);

            return RunResult::createError($e, $output);
        }

        if ($target && $this->isEntityChanged($target)) {
            $this->entityManager->saveEntity($target);
        }

        $output = $variables->__output ?? null;

        return RunResult::createSuccess($output);
    }

    private function isEntityChanged(Entity $entity): bool
    {
        foreach ($entity->getAttributeList() as $attribute) {
            if ($entity->isAttributeChanged($attribute)) {
                return true;
            }
        }

        return false;
    }
}
