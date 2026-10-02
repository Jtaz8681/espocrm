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

namespace Espo\Core\Acl\LinkChecker;

use Espo\Core\Acl\LinkChecker;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use RuntimeException;

class LinkCheckerFactory
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * Create a link checker.
     *
     * @return LinkChecker<Entity, Entity>
     */
    public function create(string $scope, string $link): LinkChecker
    {
        $className = $this->getClassName($scope, $link);

        if (!$className) {
            throw new RuntimeException("Link checker is not implemented for {$scope}.{$link}.");
        }

        return $this->injectableFactory->create($className);
    }

    public function isCreatable(string $scope, string $link): bool
    {
        return (bool) $this->getClassName($scope, $link);
    }

    /**
     * @return ?class-string<LinkChecker<Entity, Entity>>
     */
    private function getClassName(string $scope, string $link): ?string
    {
        /** @var ?class-string<LinkChecker<Entity, Entity>> */
        return $this->metadata->get(['aclDefs', $scope, 'linkCheckerClassNameMap', $link]);
    }
}
