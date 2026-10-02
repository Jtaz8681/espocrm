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

use Espo\Core\Field\Link;
use Espo\Core\ORM\Entity;

use LogicException;

class ImportError extends Entity
{
    public const ENTITY_TYPE = 'ImportError';

    public const TYPE_VALIDATION = 'Validation';
    public const TYPE_NO_ACCESS = 'No-Access';
    public const TYPE_NOT_FOUND = 'Not-Found';
    public const TYPE_INTEGRITY_CONSTRAINT_VIOLATION = 'Integrity-Constraint-Violation';

    /**
     * @return self::TYPE_*|null
     */
    public function getType(): ?string
    {
        return $this->get('type');
    }

    public function getExportRowIndex(): int
    {
        return $this->get('exportRowIndex');
    }

    public function getRowIndex(): int
    {
        return $this->get('rowIndex');
    }

    public function getValidationField(): ?string
    {
        return $this->get('validationField');
    }

    public function getValidationType(): ?string
    {
        return $this->get('validationType');
    }

    /**
     * @return string[]
     */
    public function getRow(): array
    {
        /** @var ?string[] $value */
        $value = $this->get('row');

        if ($value === null) {
            throw new LogicException();
        }

        return $value;
    }

    public function getImportLink(): Link
    {
        /** @var ?Link $link */
        $link = $this->getValueObject('import');

        if ($link === null) {
            throw new LogicException();
        }

        return $link;
    }
}
