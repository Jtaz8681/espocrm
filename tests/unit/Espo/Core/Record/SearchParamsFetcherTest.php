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

namespace tests\unit\Espo\Core\Record;

use Espo\Core\Record\SearchParamsFetcher;
use Espo\Core\Api\RequestWrapper;

use Espo\Core\Utils\Config;
use Espo\Core\Select\Text\MetadataProvider as TextMetadataProvider;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;

class SearchParamsFetcherTest extends TestCase
{
    private $config;
    private $textMetadataProvider;

    protected function setUp(): void
    {
        $this->config = $this->createMocK(Config::class);
        $this->textMetadataProvider = $this->createMocK(TextMetadataProvider::class);

        $this->config
            ->method('get')
            ->with('recordListMaxSizeLimit')
            ->willReturn(null);
    }

    public function testFetchJson1(): void
    {
        $raw = [
            'textFilter' => 'test*',
            'maxSize' => 10,
        ];

        $q = http_build_query(['searchParams' => json_encode($raw)]);

        $request = (new RequestFactory)->createRequest('GET', 'http://localhost/?' . $q);

        $fetcher = new SearchParamsFetcher($this->config, $this->textMetadataProvider);

        $params = $fetcher->fetch(new RequestWrapper($request));

        $this->assertEquals($raw['textFilter'], $params->getTextFilter());
        $this->assertEquals($raw['maxSize'], $params->getMaxSize());
    }

    public function testFetchQuery(): void
    {
        $q = http_build_query(['attributeSelect' => 'a,b']);

        $request = (new RequestFactory)->createRequest('GET', 'http://localhost/?' . $q);

        $fetcher = new SearchParamsFetcher($this->config, $this->textMetadataProvider);

        $params = $fetcher->fetch(new RequestWrapper($request));

        $this->assertEquals(['a', 'b'], $params->getSelect());
    }
}
