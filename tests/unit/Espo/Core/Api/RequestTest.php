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

namespace tests\unit\Espo\Core\Api;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Psr7Request;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Factory\RequestFactory;
use Espo\Core\Api\RequestWrapper;

class RequestTest extends TestCase
{
    private $request;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(Psr7Request::class)->disableOriginalConstructor()->getMock();
    }

    protected function createRequest(array $queryParams, array $routeParams = []) : RequestWrapper
    {
        $this->request
            ->expects($this->any())
            ->method('getQueryParams')
            ->willReturn($queryParams);

        return new RequestWrapper($this->request, '', $routeParams);
    }

    public function testHasQueryParam()
    {
        $request = $this->createRequest([
            'id' => '1',
        ]);

        $this->assertTrue($request->hasQueryParam('id'));
        $this->assertFalse($request->hasQueryParam('test'));
    }

    public function testHasRouteParam()
    {
        $request = $this->createRequest(
            [
            ],
            [
                'id' => '1',
            ]
        );

        $this->assertTrue($request->hasRouteParam('id'));
        $this->assertFalse($request->hasRouteParam('test'));
    }

    public function testGetQueryParam()
    {
        $request = $this->createRequest([
            'id' => '1',
        ]);

        $this->assertEquals('1', $request->getQueryParam('id'));
    }

    public function testGetRouteParam()
    {
        $request = $this->createRequest(
            [
            ],
            [
                'id' => '1',
            ]
        );

        $this->assertEquals('1', $request->getRouteParam('id'));
    }

    public function testGet()
    {
        $request = $this->createRequest(
            [
                'id' => '1',
            ],
            [
                'id' => '2',
            ]
        );

        $this->assertEquals('2', $request->get('id'));
    }

    protected function createRequestWithBody(string $contents) : RequestWrapper
    {
        $body = $this->createMock(StreamInterface::class);

        $body
            ->expects($this->any())
            ->method('getContents')
            ->willReturn($contents);

        $this->request
            ->expects($this->any())
            ->method('getBody')
            ->willReturn($body);

        $this->request
            ->expects($this->any())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(true);

        $this->request
            ->expects($this->any())
            ->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['application/json']);

        return new RequestWrapper($this->request);
    }

    public function testGetParsedBody()
    {
        $original = (object) [
            'key1' => '1',
            'key2' => (object) [
                'key21' => [
                    '211',
                    '212',
                    (object) [
                        '2111' => '1',
                    ],
                ],
            ],
            'key3' => [
                '31',
                '32',
                null,
            ],
            'key4' => null,
        ];

        $contents = json_encode($original);

        $request = $this->createRequestWithBody($contents);

        $parsed = $request->getParsedBody();

        $anotherParsed = $request->getParsedBody();

        $this->assertEquals($parsed, $original);

        $this->assertEquals($parsed, $anotherParsed);

        $this->assertNotSame($parsed, $anotherParsed);

        $this->assertNotSame($parsed->key2, $anotherParsed->key2);

        $this->assertNotSame($parsed->key2->key21[2], $anotherParsed->key2->key21[2]);
    }

    public function testContentType1(): void
    {
        $request = (new RequestFactory())
            ->createRequest('POST', 'http://localhost/?')
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $requestWrapped = new RequestWrapper($request);

        $this->assertEquals('application/json', $requestWrapped->getContentType());
    }

    public function testContentType2(): void
    {
        $request = (new RequestFactory())
            ->createRequest('POST', 'http://localhost/?')
            ->withHeader('Content-Type', 'application/json');

        $requestWrapped = new RequestWrapper($request);

        $this->assertEquals('application/json', $requestWrapped->getContentType());
    }

    public function testContentTypeEmpty(): void
    {
        $request = (new RequestFactory())
            ->createRequest('POST', 'http://localhost/?');

        $requestWrapped = new RequestWrapper($request);

        $this->assertEquals(null, $requestWrapped->getContentType());
    }

    public function testHeaderAsArray(): void
    {
        $request = (new RequestFactory())
            ->createRequest('POST', 'http://localhost/?')
            ->withAddedHeader('Test', '1')
            ->withAddedHeader('Test', '2');

        $requestWrapped = new RequestWrapper($request);

        $this->assertEquals(['1', '2'], $requestWrapped->getHeaderAsArray('Test'));
    }
}
