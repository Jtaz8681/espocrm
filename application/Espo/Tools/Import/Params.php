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

namespace Espo\Tools\Import;

use Espo\Core\Utils\ObjectUtil;
use stdClass;
use TypeError;

/**
 * Immutable.
 */
class Params
{
    public const string ACTION_CREATE = 'create';
    public const string ACTION_CREATE_AND_UPDATE = 'createAndUpdate';
    public const string ACTION_UPDATE = 'update';

    private ?string $action = null;
    private ?string $delimiter = null;
    private ?string $textQualifier = null;
    private ?string $personNameFormat = null;
    private bool $idleMode = false;
    private bool $manualMode = false;
    private bool $silentMode = false;
    private bool $headerRow = false;
    private bool $skipDuplicateChecking = false;
    private bool $startFromLastIndex = false;
    /**
     * @var int[]
     */
    private $updateBy = [];
    private stdClass $defaultValues;
    private ?string $dateFormat = null;
    private ?string $timeFormat = null;
    private ?string $currency = null;
    private ?string $timezone = null;
    private ?string $decimalMark = null;
    private ?string $phoneNumberCountry = null;
    /** @var string[]|null */
    private ?array $defaultFieldList = null;

    private function __construct()
    {
        $this->defaultValues = (object) [];
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getDelimiter(): ?string
    {
        return $this->delimiter;
    }

    public function getTextQualifier(): ?string
    {
        return $this->textQualifier;
    }

    public function getPersonNameFormat(): ?string
    {
        return $this->personNameFormat;
    }

    public function getPhoneNumberCountry(): ?string
    {
        return $this->phoneNumberCountry;
    }

    public function isIdleMode(): bool
    {
        return $this->idleMode;
    }

    public function isManualMode(): bool
    {
        return $this->manualMode;
    }

    public function isSilentMode(): bool
    {
        return $this->silentMode;
    }

    public function headerRow(): bool
    {
        return $this->headerRow;
    }

    public function skipDuplicateChecking(): bool
    {
        return $this->skipDuplicateChecking;
    }

    public function startFromLastIndex(): bool
    {
        return $this->startFromLastIndex;
    }

    /**
     * @return int[]
     */
    public function getUpdateBy(): array
    {
        return $this->updateBy;
    }

    public function getDefaultValues(): stdClass
    {
        return ObjectUtil::clone($this->defaultValues);
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function getTimeFormat(): ?string
    {
        return $this->timeFormat;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getDecimalMark(): ?string
    {
        return $this->decimalMark;
    }

    public static function create(): self
    {
        return new self();
    }

    public function withAction(?string $action): self
    {
        $obj = clone $this;
        $obj->action = $action;

        return $obj;
    }

    public function withDelimiter(?string $delimiter): self
    {
        $obj = clone $this;
        $obj->delimiter = $delimiter;

        return $obj;
    }

    public function withTextQualifier(?string $textQualifier): self
    {
        $obj = clone $this;
        $obj->textQualifier = $textQualifier;

        return $obj;
    }

    public function withPersonNameFormat(?string $personNameFormat): self
    {
        $obj = clone $this;
        $obj->personNameFormat = $personNameFormat;

        return $obj;
    }

    public function withPhoneNumberCountry(?string $phoneNumberCountry): self
    {
        $obj = clone $this;
        $obj->phoneNumberCountry = $phoneNumberCountry;

        return $obj;
    }

    public function withIdleMode(bool $idleMode = true): self
    {
        $obj = clone $this;
        $obj->idleMode = $idleMode;

        return $obj;
    }

    public function withManualMode(bool $manualMode = true): self
    {
        $obj = clone $this;
        $obj->manualMode = $manualMode;

        return $obj;
    }

    public function withSilentMode(bool $silentMode = true): self
    {
        $obj = clone $this;
        $obj->silentMode = $silentMode;

        return $obj;
    }

    public function withHeaderRow(bool $headerRow = true): self
    {
        $obj = clone $this;
        $obj->headerRow = $headerRow;

        return $obj;
    }

    public function withSkipDuplicateChecking(bool $skipDuplicateChecking = true): self
    {
        $obj = clone $this;
        $obj->skipDuplicateChecking = $skipDuplicateChecking;

        return $obj;
    }

    public function withStartFromLastIndex(bool $startFromLastIndex = true): self
    {
        $obj = clone $this;
        $obj->startFromLastIndex = $startFromLastIndex;

        return $obj;
    }

    /**
     * @param string[] $fieldList
     * @return self
     */
    public function withDefaultFieldList(?array $fieldList = null): self
    {
        $obj = clone $this;
        $obj->defaultFieldList = $fieldList;

        return $obj;
    }

    /**
     * @param int[] $updateBy
     */
    public function withUpdateBy(array $updateBy): self
    {
        $obj = clone $this;
        $obj->updateBy = $updateBy;

        return $obj;
    }

    /**
     * @param stdClass|array<string, mixed>|null $defaultValues
     */
    public function withDefaultValues($defaultValues): self
    {
        if (is_array($defaultValues)) {
            $defaultValues = (object) $defaultValues;
        }

        if (is_null($defaultValues)) {
            $defaultValues = (object) [];
        }

        /** @var stdClass|scalar $defaultValues */

        if (!is_object($defaultValues)) {
            throw new TypeError();
        }

        $obj = clone $this;
        $obj->defaultValues = $defaultValues;

        return $obj;
    }

    public function withDateFormat(?string $dateFormat): self
    {
        $obj = clone $this;
        $obj->dateFormat = $dateFormat;

        return $obj;
    }

    public function withTimeFormat(?string $timeFormat): self
    {
        $obj = clone $this;
        $obj->timeFormat = $timeFormat;

        return $obj;
    }

    public function withCurrency(?string $currency): self
    {
        $obj = clone $this;
        $obj->currency = $currency;

        return $obj;
    }

    public function withTimezone(?string $timezone): self
    {
        $obj = clone $this;
        $obj->timezone = $timezone;

        return $obj;
    }

    public function withDecimalMark(?string $decimalMark): self
    {
        $obj = clone $this;
        $obj->decimalMark = $decimalMark;

        return $obj;
    }

    /**
     * @param stdClass|array<string, mixed>|null $params
     */
    public static function fromRaw($params): self
    {
        if ($params === null) {
            $params = (object) [];
        }

        $raw = (object) $params;

        return self::create()
            ->withAction($raw->action ?? null)
            ->withCurrency($raw->currency ?? null)
            ->withDateFormat($raw->dateFormat ?? null)
            ->withDecimalMark($raw->decimalMark ?? null)
            ->withDefaultValues($raw->defaultValues ?? null)
            ->withDefaultFieldList($raw->defaultFieldList ?? null)
            ->withDelimiter($raw->delimiter ?? null)
            ->withHeaderRow($raw->headerRow ?? false)
            ->withIdleMode($raw->idleMode ?? false)
            ->withManualMode($raw->manualMode ?? false)
            ->withPersonNameFormat($raw->personNameFormat ?? null)
            ->withPhoneNumberCountry($raw->phoneNumberCountry ?? null)
            ->withSilentMode($raw->silentMode ?? false)
            ->withSkipDuplicateChecking($raw->skipDuplicateChecking ?? false)
            ->withStartFromLastIndex($raw->startFromLastIndex ?? false)
            ->withTextQualifier($raw->textQualifier ?? null)
            ->withTimeFormat($raw->timeFormat ?? false)
            ->withTimezone($raw->timezone ?? null)
            ->withUpdateBy($raw->updateBy ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return [
            'action' => $this->action,
            'currency' => $this->currency,
            'dateFormat' => $this->dateFormat,
            'decimalMark' => $this->decimalMark,
            'defaultValues' => $this->defaultValues,
            'delimiter' => $this->delimiter,
            'headerRow' => $this->headerRow,
            'idleMode' => $this->idleMode,
            'manualMode' => $this->manualMode,
            'personNameFormat' => $this->personNameFormat,
            'silentMode' => $this->silentMode,
            'skipDuplicateChecking' => $this->skipDuplicateChecking,
            'startFromLastIndex' => $this->startFromLastIndex,
            'textQualifier' => $this->textQualifier,
            'phoneNumberCountry' => $this->phoneNumberCountry,
            'timeFormat' => $this->timeFormat,
            'timezone' => $this->timezone,
            'updateBy' => $this->updateBy,
            'defaultFieldList' => $this->defaultFieldList,
        ];
    }
}
