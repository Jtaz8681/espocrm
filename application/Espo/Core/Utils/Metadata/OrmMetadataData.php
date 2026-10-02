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

namespace Espo\Core\Utils\Metadata;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Database\Orm\Converter;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\Util;

class OrmMetadataData
{
    /** @var ?array<string, array<string, mixed>> */
    private $data = null;
    private string $cacheKey = 'ormMetadata';
    private bool $useCache;
    private ?Converter $converter = null;

    public function __construct(
        private DataCache $dataCache,
        private InjectableFactory $injectableFactory,
        Config\SystemConfig $systemConfig,
    ) {
        $this->useCache = $systemConfig->useCache();
    }

    private function getConverter(): Converter
    {
        if (!isset($this->converter)) {
            $this->converter = $this->injectableFactory->create(Converter::class);
        }

        return $this->converter;
    }

    /**
     * Reloads data.
     */
    public function reload(): void
    {
        $this->getDataInternal(true);
    }

    /**
     * Get raw data.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getData(): array
    {
        return $this->getDataInternal();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getDataInternal(bool $reload = false): array
    {
        if (isset($this->data) && !$reload) {
            return $this->data;
        }

        if ($this->useCache && $this->dataCache->has($this->cacheKey) && !$reload) {
            /** @var array<string, array<string, mixed>> $data */
            $data = $this->dataCache->get($this->cacheKey);

            $this->data = $data;

            return $this->data;
        }

        $this->data = $this->getConverter()->process();

        if ($this->useCache) {
            $this->dataCache->store($this->cacheKey, $this->data);
        }

        return $this->data;
    }

    /**
     * @param string|string[]|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return Util::getValueByKey($this->getData(), $key, $default);
    }
}
