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

namespace Espo\Tools\Export\Format\Xlsx;

use Espo\Core\Exceptions\Error;
use Espo\Tools\Export\Collection;
use Espo\Tools\Export\Processor as ProcessorInterface;
use Espo\Tools\Export\Processor\Params;

use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Psr\Http\Message\StreamInterface;

class Processor implements ProcessorInterface
{
    private const PARAM_LITE = 'lite';

    public function __construct(
        private PhpSpreadsheetProcessor $phpSpreadsheetProcessor,
        private OpenSpoutProcessor $openSpoutProcessor,
    ) {}

    /**
     * @throws Error
     */
    public function process(Params $params, Collection $collection): StreamInterface
    {
        return $params->getParam(self::PARAM_LITE) ?
            $this->processOpenSpout($params, $collection) :
            $this->processPhpSpreadsheet($params, $collection);
    }

    /**
     * @throws Error
     */
    private function processPhpSpreadsheet(Params $params, Collection $collection): StreamInterface
    {
        try {
            return $this->phpSpreadsheetProcessor->process($params, $collection);
        } catch (SpreadsheetException|WriterException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * @throws Error
     */
    private function processOpenSpout(Params $params, Collection $collection): StreamInterface
    {
        try {
            return $this->openSpoutProcessor->process($params, $collection);
        } catch (\Throwable $e) {
            throw new Error($e->getMessage());
        }
    }
}
