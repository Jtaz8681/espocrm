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

namespace Espo\Core\WebSocket;

use Espo\Core\Utils\Log;
use Espo\Core\Utils\Json;

use stdClass;
use Throwable;

class Submission
{
    public function __construct(
        private Sender $sender,
        private Log $log,
        private ConfigDataProvider $configDataProvider,
    ) {}

    /**
     * Submit to a web-socket server.
     *
     * Since 9.1.0 performs check whether enabled in the config.
     *
     * @param stdClass|array<string, mixed>|null $data Data to submit. Assoc array is supported since 9.1.0.
     */
    public function submit(string $topic, ?string $userId = null, stdClass|array|null $data = null): void
    {
        if (!$this->configDataProvider->isEnabled()) {
            return;
        }

        if (!$data) {
            $data = (object) [];
        }

        if (is_array($data)) {
            $data = (object) $data;
        }

        if ($userId) {
            $data->userId = $userId;
        }

        $data->topicId = $topic;

        $message = Json::encode($data);

        try {
            $this->sender->send($message);
        } catch (Throwable $e) {
            $this->log->error("WebSocketSubmission: " . $e->getMessage());
        }
    }
}
