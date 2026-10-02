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

namespace Espo\Tools\MassUpdate;

use Espo\Core\MassAction\Data as ActionData;
use Espo\Core\Utils\ObjectUtil;

use RuntimeException;
use stdClass;

class Data
{
    private stdClass $values;

    /**
     * @var array<string, Action::*>
     */
    private array $actions;

    /**
     * @param array<string, Action::*> $actions
     */
    private function __construct(stdClass $values, array $actions)
    {
        $this->values = $values;
        $this->actions = $actions;
    }

    public function has(string $attribute): bool
    {
        return property_exists($this->values, $attribute);
    }

    /**
     * @return string[]
     */
    public function getAttributeList(): array
    {
        return array_keys(get_object_vars($this->values));
    }

    /**
     * @return mixed
     */
    public function getValue(string $attribute)
    {
        return $this->getValues()->$attribute ?? null;
    }

    public function getValues(): stdClass
    {
        return ObjectUtil::clone($this->values);
    }

    /**
     * @return Action::*|null
     */
    public function getAction(string $attribute): ?string
    {
        if (!$this->has($attribute)) {
            return null;
        }

        return $this->actions[$attribute] ?? Action::UPDATE;
    }

    public static function create(): self
    {
        return new self((object) [], []);
    }

    public static function fromMassActionData(ActionData $data): self
    {
        $values = $data->get('values');
        $rawActions = $data->get('actions');

        // Backward compatibility.
        if (!$data->has('values')) {
            return new self($data->getRaw(), []);
        }

        if (!$values instanceof stdClass) {
            throw new RuntimeException("No `values` in mass-action data.");
        }

        if ($rawActions !== null && !$rawActions instanceof stdClass) {
            throw new RuntimeException("Bad `actions` in mass-action data.");
        }

        if ($rawActions === null) {
            $rawActions = (object) [];
        }

        return new self($values, get_object_vars($rawActions));
    }

    /**
     * @param mixed $value
     * @param Action::*|null $action If NULL, the current action will be used. If no current, then 'update'.
     */
    public function with(string $attribute, $value, ?string $action = null): self
    {
        if ($action === null) {
            $action = $this->getAction($attribute) ?? Action::UPDATE;
        }

        $values = $this->getValues();
        $actions = $this->actions;

        $values->$attribute = $value;
        $actions[$attribute] = $action;

        return new self($values, $actions);
    }

    public function without(string $attribute): self
    {
        $values = $this->getValues();
        $actions = $this->actions;

        unset($values->$attribute);
        unset($actions[$attribute]);

        return new self($values, $actions);
    }

    public function toMassActionData(): ActionData
    {
        return ActionData::fromRaw((object) [
            'values' => $this->getValues(),
            'actions' => (object) $this->actions,
        ]);
    }

    public function __clone()
    {
        $this->values = ObjectUtil::clone($this->values);
    }
}
