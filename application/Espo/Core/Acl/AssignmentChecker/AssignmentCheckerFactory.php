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

namespace Espo\Core\Acl\AssignmentChecker;

use Espo\Core\Acl\AssignmentChecker;
use Espo\Core\Acl\DefaultAssignmentChecker;
use Espo\Core\Acl\Exceptions\NotImplemented;
use Espo\Core\InjectableFactory;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;

class AssignmentCheckerFactory
{
    /** @var class-string<AssignmentChecker<CoreEntity>> */
    private string $defaultClassName = DefaultAssignmentChecker::class;

    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * Create an access checker.
     *
     * @return AssignmentChecker<Entity>
     * @throws NotImplemented
     */
    public function create(string $scope): AssignmentChecker
    {
        $className = $this->getClassName($scope);

        return $this->injectableFactory->create($className);
    }

    /**
     * @return class-string<AssignmentChecker<Entity>>
     * @throws NotImplemented
     */
    private function getClassName(string $scope): string
    {
        /** @var ?class-string<AssignmentChecker<Entity>> $className */
        $className = $this->metadata->get(['aclDefs', $scope, 'assignmentCheckerClassName']);

        if ($className) {
            return $className;
        }

        if (!$this->metadata->get(['scopes', $scope])) {
            throw new NotImplemented();
        }

        /** @var class-string<AssignmentChecker<Entity>> */
        return $this->defaultClassName;
    }
}
