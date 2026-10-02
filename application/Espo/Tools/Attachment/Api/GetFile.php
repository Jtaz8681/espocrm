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

namespace Espo\Tools\Attachment\Api;

use Espo\Core\Api\Action as ActionAlias;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Tools\Attachment\Service;

/**
 * Download a file.
 */
class GetFile implements ActionAlias
{
    public function __construct(private Service $service) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest();
        }

        $fileData = $this->service->getFileData($id);

        $fileName = str_replace("\"", "\\\"", $fileData->getName() ?? '');

        $response = ResponseComposer::empty()
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setHeader('Content-Length', (string) $fileData->getSize())
            ->setBody($fileData->getStream());

        if ($fileData->getType()) {
            $response->setHeader('Content-Type', $fileData->getType());
        }

        return $response;
    }
}
