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

namespace Espo\Entities;

use Espo\Core\Field\DateTime;
use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;

use stdClass;
use LogicException;

class UniqueId extends Entity
{
    public const ENTITY_TYPE = 'UniqueId';

    public function getIdValue(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function getTerminateAt(): ?DateTime
    {
        /** @var ?DateTime */
        return $this->getValueObject('terminateAt');
    }

    public function getData(): stdClass
    {
        return $this->get('data') ?? (object) [];
    }

    public function getCreatedAt(): DateTime
    {
        /** @var ?DateTime $value */
        $value = $this->getValueObject(Field::CREATED_AT);

        if (!$value) {
            throw new LogicException();
        }

        return $value;
    }

    /**
     * @param array<string, mixed>|stdClass $data
     */
    public function setData(array|stdClass $data): self
    {
        $this->set('data', $data);

        return $this;
    }

    public function setTarget(?LinkParent $target): self
    {
        if (!$target) {
            $this->setMultiple([
                'targetId' => null,
                'targetType' => null,
            ]);
        } else {
            $this->setMultiple([
                'targetId' => $target->getId(),
                'targetType' => $target->getEntityType(),
            ]);
        }

        return $this;
    }

    public function setTerminateAt(?DateTime $terminateAt): self
    {
        $this->setValueObject('terminateAt', $terminateAt);

        return $this;
    }
}
