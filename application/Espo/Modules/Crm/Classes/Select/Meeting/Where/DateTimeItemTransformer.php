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

namespace Espo\Modules\Crm\Classes\Select\Meeting\Where;

use Espo\Core\Select\Where\DateTimeItemTransformer as DateTimeItemTransformerInterface;
use Espo\Core\Select\Where\DefaultDateTimeItemTransformer;
use Espo\Core\Select\Where\Item;

/**
 * Extends to take into account DateStartDate and DateEndDate fields.
 *
 * @noinspection PhpUnused
 */
class DateTimeItemTransformer implements DateTimeItemTransformerInterface
{
    public function __construct(
        private DefaultDateTimeItemTransformer $defaultDateTimeItemTransformer
    ) {}

    public function transform(Item $item): Item
    {
        $type = $item->getType();
        $value = $item->getValue();
        $attribute = $item->getAttribute();

        $transformedItem = $this->defaultDateTimeItemTransformer->transform($item);

        if (
            !in_array($attribute, ['dateStart', 'dateEnd']) ||
            in_array($type, [
                Item\Type::IS_NULL,
                Item\Type::EVER,
                Item\Type::IS_NOT_NULL,
            ])
        ) {
            return $transformedItem;
        }

        $attributeDate = $attribute . 'Date';

        if (is_string($value)) {
            if (strlen($value) > 11) {
                return $transformedItem;
            }
        } else if (is_array($value)) {
            foreach ($value as $valueItem) {
                if (is_string($valueItem) && strlen($valueItem) > 11) {
                    return $transformedItem;
                }
            }
        }

        $datePartRaw = [
            'attribute' => $attributeDate,
            'type' => $type,
            'value' => $value,
        ];

        $data = $item->getData();

        if ($data instanceof Item\Data\DateTime) {
            $datePartRaw['timeZone'] = $data->getTimeZone();
        }

        $raw = [
            'type' => Item::TYPE_OR,
            'value' => [
                $datePartRaw,
                [
                    'type' => Item::TYPE_AND,
                    'value' => [
                        $transformedItem->getRaw(),
                        [
                            'type' => Item\Type::IS_NULL,
                            'attribute' => $attributeDate,
                        ]
                    ]

                ]
            ]
        ];

        return Item::fromRaw($raw);
    }
}
