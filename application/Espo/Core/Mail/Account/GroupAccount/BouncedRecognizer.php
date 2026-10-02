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

namespace Espo\Core\Mail\Account\GroupAccount;

use Espo\Core\Mail\Exceptions\ImapError;
use Espo\Core\Mail\Message;
use Espo\Core\Mail\Message\Part;

use const PREG_SPLIT_NO_EMPTY;

class BouncedRecognizer
{
    /** @var string[] */
    private array $hardBounceCodeList = [
        '5.0.0',
        '5.1.1', // bad destination mailbox address
        '5.1.2', // bad destination system address
        '5.1.6', // destination mailbox has moved, no forwarding address
        '5.4.1', // no answer from host
    ];

    public function isBounced(Message $message): bool
    {
        $from = $message->getHeader('From');
        $contentType = $message->getHeader('Content-Type');

        if (preg_match('/MAILER-DAEMON|POSTMASTER/i', $from ?? '')) {
            return true;
        }

        if (str_starts_with($contentType ?? '', 'multipart/report')) {
            // @todo Check whether ever works.
            $deliveryStatusPart = $this->getDeliveryStatusPart($message);

            if ($deliveryStatusPart) {
                return true;
            }

            try {
                $content = $message->getRawContent();
            } catch (ImapError) {
                return false;
            }

            if (
                str_contains($content, 'message/delivery-status') &&
                str_contains($content, 'Status: ')
            ) {
                return true;
            }
        }

        return false;
    }

    public function isHard(Message $message): bool
    {
        $content = $message->getRawContent();

        /** @noinspection RegExpSimplifiable */
        /** @noinspection RegExpDuplicateCharacterInClass */
        if (preg_match('/permanent[ ]*[error|failure]/', $content)) {
            return true;
        }

        $m = null;

        $has5xxStatus = preg_match('/Status: (5\.[0-9]\.[0-9])/', $content, $m);

        if ($has5xxStatus) {
            $status = $m[1] ?? null;

            if (in_array($status, $this->hardBounceCodeList)) {
                return true;
            }
        }

        return false;
    }

    public function extractStatus(Message $message): ?string
    {
        $content = $message->getRawContent();

        $m = null;

        $hasStatus = preg_match('/Status: ([0-9]\.[0-9]\.[0-9])/', $content, $m);

        if ($hasStatus) {
            return $m[1] ?? null;
        }

        return null;
    }

    public function extractQueueItemId(Message $message): ?string
    {
        $content = $message->getRawContent();

        if (preg_match('/X-Queue-Item-Id: [a-z0-9\-]*/', $content, $m)) {
            /** @var array{string} $arr */
            $arr = preg_split('/X-Queue-Item-Id: /', $m[0], -1, PREG_SPLIT_NO_EMPTY);

            return $arr[0];
        }

        $to = $message->getHeader('to');

        if (preg_match('/\+bounce-qid-[a-z0-9\-]*/', $to ?? '', $m)) {
            /** @var array{string} $arr */
            $arr = preg_split('/\+bounce-qid-/', $m[0], -1, PREG_SPLIT_NO_EMPTY);

            return $arr[0];
        }

        return null;
    }

    private function getDeliveryStatusPart(Message $message): ?Part
    {
        foreach ($message->getPartList() as $part) {
            if ($part->getContentType() === 'message/delivery-status') {
                return $part;
            }
        }

        return null;
    }
}
