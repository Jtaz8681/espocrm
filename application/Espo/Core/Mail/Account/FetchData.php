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

namespace Espo\Core\Mail\Account;

use Espo\Core\Utils\ObjectUtil;
use Espo\Core\Field\DateTime;

use stdClass;
use RuntimeException;

class FetchData
{
    private stdClass $data;

    /**
     * @internal
     */
    public function __construct(
        ?stdClass $data = null,
        private int $validityNumber = 0,
    ) {
        $this->data = ObjectUtil::clone($data ?? (object) []);
    }

    public static function fromRaw(stdClass $data, int $fetchNumber): self
    {
        return new self($data, $fetchNumber);
    }

    public function getRaw(): stdClass
    {
        return ObjectUtil::clone($this->data);
    }

    public function getLastUid(string $folder): ?int
    {
        $id = $this->data->lastUID->$folder ?? null;

        if ($id === null) {
            return null;
        }

        // To int for bc. It used to be string.
        return (int) $id;
    }

    public function getUidValidity(string $folder): ?int
    {
        $id = $this->data->uidValidity->$folder ?? null;

        if (!is_int($id)) {
            return null;
        }

        return $id;
    }

    public function setUidValidity(string $folder, ?int $uid): void
    {
        if (!property_exists($this->data, 'uidValidity')) {
            $this->data->uidValidity = (object) [];
        }

        $this->data->uidValidity->$folder = $uid;
    }

    public function getLastDate(string $folder): ?DateTime
    {
        $value = $this->data->lastDate->$folder ?? null;

        if ($value === null) {
            return null;
        }

        // For backward compatibility.
        if ($value === 0) {
            return null;
        }

        if (!is_string($value)) {
            throw new RuntimeException("Bad value in fetch-data.");
        }

        return DateTime::fromString($value);
    }

    public function getForceByDate(string $folder): bool
    {
        return $this->data->byDate->$folder ?? false;
    }

    public function setLastUid(string $folder, ?int $uniqueId): void
    {
        if (!property_exists($this->data, 'lastUID')) {
            $this->data->lastUID = (object) [];
        }

        $this->data->lastUID->$folder = $uniqueId;
    }

    public function setLastDate(string $folder, ?DateTime $lastDate): void
    {
        if (!property_exists($this->data, 'lastDate')) {
            $this->data->lastDate = (object) [];
        }

        if ($lastDate === null) {
            $this->data->lastDate->$folder = null;

            return;
        }

        $this->data->lastDate->$folder = $lastDate->toString();
    }

    public function setForceByDate(string $folder, bool $forceByDate): void
    {
        if (!property_exists($this->data, 'byDate')) {
            $this->data->byDate = (object) [];
        }

        $this->data->byDate->$folder = $forceByDate;
    }

    /**
     * @since 10.0.0
     */
    public function getValidityNumber(): int
    {
        return $this->validityNumber;
    }
}
