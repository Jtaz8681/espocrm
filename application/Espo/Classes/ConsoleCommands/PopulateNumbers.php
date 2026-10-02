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

namespace Espo\Classes\ConsoleCommands;

use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\Exceptions\ArgumentNotSpecified;
use Espo\Core\Console\Exceptions\InvalidArgument;
use Espo\Core\Console\IO;
use Espo\Core\Exceptions\Error;
use Espo\Core\FieldProcessing\NextNumber\Processor;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Order;

class PopulateNumbers implements Command
{
    private Processor $beforeSaveProcessor;
    private EntityManager $entityManager;

    public function __construct(
        Processor $beforeSaveProcessor,
        EntityManager $entityManager
    ) {
        $this->beforeSaveProcessor = $beforeSaveProcessor;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Error
     */
    public function run(Params $params, IO $io): void
    {
        $entityType = $params->getArgument(0);
        $field = $params->getArgument(1);

        $orderBy = $params->getOption('orderBy') ?? Field::CREATED_AT;
        $order = strtoupper($params->getOption('order') ?? Order::ASC);

        if (!$entityType) {
            throw new ArgumentNotSpecified("No entity type argument.");
        }

        if (!$field) {
            throw new ArgumentNotSpecified("No field argument.");
        }

        if ($order !== Order::ASC && $order !== Order::DESC) {
            throw new InvalidArgument("Bad order option.");
        }

        $fieldType = $this->entityManager
            ->getDefs()
            ->getEntity($entityType)
            ->getField($field)
            ->getType();

        if ($fieldType !== 'number') {
            throw new InvalidArgument("Field `{$field}` is not of `number` type.");
        }

        $collection = $this->entityManager
            ->getRDBRepository($entityType)
            ->where([
                $field => null,
            ])
            ->order($orderBy, $order)
            ->sth()
            ->find();

        foreach ($collection as $i => $entity) {
            if (!$entity instanceof CoreEntity) {
                throw new Error();
            }

            $this->beforeSaveProcessor->processPopulate($entity, $field);
            $this->entityManager->saveEntity($entity, [SaveOption::IMPORT => true]);

            if ($i % 1000 === 0) {
                $io->write('.');
            }
        }

        $io->writeLine('');
        $io->writeLine('Done.');
    }
}
