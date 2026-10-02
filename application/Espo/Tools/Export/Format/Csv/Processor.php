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

namespace Espo\Tools\Export\Format\Csv;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Json;
use Espo\Entities\Preferences;
use Espo\ORM\Entity;
use Espo\Tools\Export\Collection;
use Espo\Tools\Export\Processor as ProcessorInterface;
use Espo\Tools\Export\Processor\Params;

use Espo\Tools\Export\Processor\Util;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Stream;

use RuntimeException;

use const JSON_UNESCAPED_UNICODE;

class Processor implements ProcessorInterface
{
    public function __construct(
        private Config $config,
        private Preferences $preferences
    ) {}

    public function process(Params $params, Collection $collection): StreamInterface
    {
        $attributeList = $params->getAttributeList();

        $delimiterRaw =
            $this->preferences->get('exportDelimiter') ??
            $this->config->get('exportDelimiter') ??
            ',';

        $delimiter = str_replace('\t', "\t", $delimiterRaw);

        $fp = fopen('php://temp', 'w');

        if ($fp === false) {
            throw new RuntimeException("Could not open temp.");
        }

        fputcsv($fp, $attributeList, $delimiter);

        foreach ($collection as $entity) {
            $preparedRow = $this->prepareRow($entity, $attributeList);

            fputcsv($fp, $preparedRow, $delimiter, '"' , "\0");
        }

        rewind($fp);

        return new Stream($fp);
    }

    /**
     * @param string[] $attributeList
     * @return string[]
     */
    private function prepareRow(Entity $entity, array $attributeList): array
    {
        $preparedRow = [];

        foreach ($attributeList as $attribute) {
            $value = $entity->get($attribute);

            if (is_array($value) || is_object($value)) {
                $value = Json::encode($value, JSON_UNESCAPED_UNICODE);
            }

            $value = (string) $value;

            $preparedRow[] = Util::sanitizeCellValue($value);
        }

        return $preparedRow;
    }
}
