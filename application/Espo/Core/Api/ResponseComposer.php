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

namespace Espo\Core\Api;

use Slim\Psr7\Factory\ResponseFactory;
use Espo\Core\Utils\Json;
use stdClass;

class ResponseComposer
{
    /**
     * Compose a JSON response.
     *
     * @param array<string|int, mixed>|stdClass|scalar|null $data A data to encode.
     */
    public static function json(mixed $data): Response
    {
        return self::empty()
            ->writeBody(Json::encode($data))
            ->setHeader('Content-Type', 'application/json');
    }

    /**
     * Compose an empty response.
     */
    public static function empty(): Response
    {
        $psr7Response = (new ResponseFactory())->createResponse();

        return new ResponseWrapper($psr7Response);
    }
}
