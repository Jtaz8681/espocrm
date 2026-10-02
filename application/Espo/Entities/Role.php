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

use Espo\Core\ORM\Entity;
use stdClass;

class Role extends Entity
{
    public const string ENTITY_TYPE = 'Role';

    /** @since 10.0.4 */
    public const string FIELD_DATA = 'data';
    /** @since 10.0.4 */
    public const string FIELD_FIELD_DATA = 'fieldData';

    public function getRawData(): stdClass
    {
        return $this->get('data') ?? (object) [];
    }

    public function getRawFieldData(): stdClass
    {
        return $this->get('fieldData') ?? (object) [];
    }

    /**
     * @param array<string, mixed>|stdClass $data
     */
    public function setRawData(array|stdClass $data): self
    {
        return $this->set('data', $data);
    }

    /**
     * @param array<string, mixed>|stdClass $fieldData
     */
    public function setRawFieldData(array|stdClass $fieldData): self
    {
        return $this->set('fieldData', $fieldData);
    }
}
