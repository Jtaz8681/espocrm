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

namespace Espo\Core\Notification;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\ClassFinder;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Util as RepositoryUtil;

class AssignmentNotificatorFactory
{
    /** @var class-string<AssignmentNotificator<Entity>> */
    protected string $defaultClassName = DefaultAssignmentNotificator::class;

    public function __construct(
        private InjectableFactory $injectableFactory,
        private ClassFinder $classFinder,
        private Metadata $metadata
    ) {}

    /**
     * @template T of Entity
     * @param class-string<T> $className An entity class name.
     * @return AssignmentNotificator<T>
     */
    public function createByClass(string $className): AssignmentNotificator
    {
        $entityType = RepositoryUtil::getEntityTypeByClass($className);

        /** @var AssignmentNotificator<T> */
        return $this->create($entityType);
    }

    /**
     * @todo Change return type to AssignmentNotificator.
     *
     * @return AssignmentNotificator<Entity>
     */
    public function create(string $entityType): object // AssignmentNotificator
    {
        $className = $this->getClassName($entityType);

        return $this->injectableFactory->create($className);
    }

    /**
     * @return class-string<AssignmentNotificator<Entity>>
     */
    private function getClassName(string $entityType): string
    {
        /** @var ?class-string<AssignmentNotificator<Entity>> $className1 */
        $className1 = $this->metadata->get(['notificationDefs', $entityType, 'assignmentNotificatorClassName']);

        if ($className1) {
            return $className1;
        }

        /* For backward compatibility. */
        /** @var ?class-string<AssignmentNotificator<Entity>> $className2 */
        $className2 = $this->classFinder->find('Notificators', $entityType);

        if ($className2) {
            return $className2;
        }

        return $this->defaultClassName;
    }
}
