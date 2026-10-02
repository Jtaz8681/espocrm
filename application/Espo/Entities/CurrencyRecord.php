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

use Espo\Core\Field\Date;
use Espo\Core\ORM\Entity;

class CurrencyRecord extends Entity
{
    public const string ENTITY_TYPE = 'CurrencyRecord';

    public const string FIELD_STATUS = 'status';
    public const string FIELD_CODE = 'code';

    public const string STATUS_ACTIVE = 'Active';
    public const string STATUS_INACTIVE = 'Inactive';

    public function getCode(): string
    {
        return $this->get(self::FIELD_CODE);
    }

    public function setCode(string $code): self
    {
        return $this->set(self::FIELD_CODE, $code);
    }

    public function getStatus(): string
    {
        return $this->get(self::FIELD_STATUS);
    }

    public function setStatus(string $status): self
    {
        return $this->set(self::FIELD_STATUS, $status);
    }

    public function setLabel(?string $label): self
    {
        return $this->set('label', $label);
    }

    public function setSymbol(?string $label): self
    {
        return $this->set('symbol', $label);
    }

    public function setIsBase(bool $isBase): self
    {
        return $this->set('isBase', $isBase);
    }

    /**
     * @param ?numeric-string $rate
     */
    public function setRate(?string $rate): self
    {
        return $this->set('rate', $rate);
    }

    public function setRateDate(?Date $date): self
    {
        return $this->setValueObject('rateDate', $date);
    }
}
