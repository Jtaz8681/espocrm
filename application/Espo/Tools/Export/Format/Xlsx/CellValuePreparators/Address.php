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

namespace Espo\Tools\Export\Format\Xlsx\CellValuePreparators;

use Espo\Core\Field\Address\AddressFactory;
use Espo\Core\Field\Address\AddressFormatterFactory;
use Espo\ORM\Entity;
use Espo\Tools\Export\Format\CellValuePreparator;

class Address implements CellValuePreparator
{
    public function __construct(
        private AddressFormatterFactory $formatterFactory
    ) {}

    public function prepare(Entity $entity, string $name): ?string
    {
        $address = (new AddressFactory())->createFromEntity($entity, $name);

        $formatter = $this->formatterFactory->createDefault();

        return $formatter->format($address) ?: null;
    }
}
