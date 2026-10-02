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
use UnexpectedValueException;

class CurrencyRecordRate extends Entity
{
    public const string ENTITY_TYPE = 'CurrencyRecordRate';

    public const string FIELD_DATE = 'date';
    public const string FIELD_BASE_CODE = 'baseCode';
    public const string FIELD_RATE = 'rate';
    public const string FIELD_RECORD = 'record';

    public const string ATTR_RECORD_ID = 'recordId';

    /**
     * @return numeric-string
     */
    public function getRate(): string
    {
        /** @var numeric-string */
        return $this->get(self::FIELD_RATE) ?? '1';
    }

    /**
     * @param numeric-string $rate
     */
    public function setRate(string $rate): self
    {
        return $this->set(self::FIELD_RATE, $rate);
    }

    public function setBaseCode(string $code): self
    {
        return $this->set(self::FIELD_BASE_CODE, $code);
    }

    public function setRecord(CurrencyRecord $record): self
    {
        return $this->setRelatedLinkOrEntity(self::FIELD_RECORD, $record);
    }

    public function setDate(Date $date): self
    {
        return $this->setValueObject(self::DATE, $date);
    }

    public function getRecord(): CurrencyRecord
    {
        $record = $this->relations->getOne(self::FIELD_RECORD);

        if (!$record instanceof CurrencyRecord) {
            throw new UnexpectedValueException("No record.");
        }

        return $record;
    }

    public function getDate(): Date
    {
        $date = $this->getValueObject(self::FIELD_DATE);

        if (!$date instanceof Date) {
            throw new UnexpectedValueException("No date.");
        }

        return $date;
    }
}
