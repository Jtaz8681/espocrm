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

namespace Espo\Core\Mail;

use Espo\Entities\Email;
use Espo\Entities\Attachment;
use Espo\Core\Mail\Message\Part;

use stdClass;

interface Parser
{
    public function hasHeader(Message $message, string $name): bool;

    public function getHeader(Message $message, string $name): ?string;

    public function getMessageId(Message $message): ?string;

    public function getAddressNameMap(Message $message): stdClass;

    /**
     * @return ?object{address: string, name: string}
     */
    public function getAddressData(Message $message, string $type): ?object;

    /**
     * @return string[]
     */
    public function getAddressList(Message $message, string $type): array;

    /**
     * @return Attachment[] A list of inline attachments.
     */
    public function getInlineAttachmentList(Message $message, Email $email): array;

    /**
     * @return Part[]
     */
    public function getPartList(Message $message): array;
}
