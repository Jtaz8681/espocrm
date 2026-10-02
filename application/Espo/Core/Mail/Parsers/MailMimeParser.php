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

namespace Espo\Core\Mail\Parsers;

use Espo\Entities\Email;
use Espo\Entities\Attachment;
use Espo\ORM\EntityManager;
use Espo\Core\Mail\Message;
use Espo\Core\Mail\Parser;
use Espo\Core\Mail\Message\Part;
use Espo\Core\Mail\Message\MailMimeParser\Part as WrapperPart;

use ZBateson\MailMimeParser\Header\AddressHeader;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\IMessage;
use ZBateson\MailMimeParser\MailMimeParser as WrappeeParser;
use ZBateson\MailMimeParser\Message\MessagePart;
use ZBateson\MailMimeParser\Message\MimePart;

use stdClass;

/**
 * An adapter for MailMimeParser library.
 */
class MailMimeParser implements Parser
{
    /** @var array<string, string> */
    private array $extMimeTypeMap = [
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'csv' => 'text/csv',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'htm' => 'text/html',
        'xml' => 'application/xml',
        'zip' => 'application/zip',
        'gz' => 'application/gzip',
        'eml' => 'message/rfc822',
        'svg' => 'image/svg+xml',
        'bmp' => 'image/bmp',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
    ];

    private ?WrappeeParser $parser = null;

    private const FIELD_BODY = 'body';
    private const FIELD_ATTACHMENTS = 'attachments';

    private const DISPOSITION_INLINE = 'inline';

    public const TYPE_MESSAGE_RFC822 = 'message/rfc822';
    public const TYPE_OCTET_STREAM  = 'application/octet-stream';

    /** @var array<string, IMessage> */
    private array $messageHash = [];

    public function __construct(private EntityManager $entityManager)
    {}

    private function getParser(): WrappeeParser
    {
        if (!$this->parser) {
            $this->parser = new WrappeeParser();
        }

        return $this->parser;
    }

    private function loadContent(Message $message): void
    {
        $raw = $message->getFullRawContent();

        $key = spl_object_hash($message);

        $this->messageHash[$key] = $this->getParser()->parse($raw, false);
    }

    /**
     * @return IMessage
     */
    private function getMessage(Message $message)
    {
        $key = spl_object_hash($message);

        if (!array_key_exists($key, $this->messageHash)) {
            $raw = $message->getRawHeader();

            if (!$raw) {
                $raw = $message->getFullRawContent();
            }

            $this->messageHash[$key] = $this->getParser()->parse($raw, false);
        }

        return $this->messageHash[$key];
    }

    public function hasHeader(Message $message, string $name): bool
    {
        return $this->getMessage($message)->getHeaderValue($name) !== null;
    }

    public function getHeader(Message $message, string $name): ?string
    {
        if (!$this->hasHeader($message, $name)) {
            return null;
        }

        return $this->getMessage($message)->getHeaderValue($name);
    }

    public function getMessageId(Message $message): ?string
    {
        $messageId = $this->getHeader($message, 'Message-ID');

        if (!$messageId) {
            return null;
        }

        if ($messageId[0] !== '<') {
            $messageId = '<' . $messageId . '>';
        }

        return $messageId;
    }

    public function getAddressNameMap(Message $message): stdClass
    {
        $map = (object) [];

        foreach (['from', 'to', 'cc', 'reply-To'] as $type) {
            $header = $this->getMessage($message)->getHeader($type);

            if (!$header || !method_exists($header, 'getAddresses')) {
                continue;
            }

            /** @var AddressHeader $header */

            $list = $header->getAddresses();

            foreach ($list as $item) {
                $address = $item->getEmail();
                $name = $item->getName();

                if ($name && $address) {
                    $map->$address = $name;
                }
            }
        }

        return $map;
    }

    public function getAddressData(Message $message, string $type): ?object
    {
        $header = $this->getMessage($message)->getHeader($type);

        /** @var ?AddressHeader $header */

        if ($header && method_exists($header, 'getAddresses')) {
            foreach ($header->getAddresses() as $item) {
                return (object) [
                    'address' => $item->getEmail(),
                    'name' => $item->getName(),
                ];
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getAddressList(Message $message, string $type): array
    {
        $addressList = [];

        $header = $this->getMessage($message)->getHeader($type);

        /** @var ?AddressHeader $header */

        if ($header && method_exists($header, 'getAddresses')) {
            $list = $header->getAddresses();

            foreach ($list as $address) {
                $addressList[] = $address->getEmail();
            }
        }

        return $addressList;
    }

    /**
     * @return Part[]
     */
    public function getPartList(Message $message): array
    {
        $wrappeeList = $this->getMessage($message)->getChildParts();

        $partList = [];

        foreach ($wrappeeList as $wrappee) {
            $partList[] = new WrapperPart($wrappee);
        }

        return $partList;
    }

    /**
     * @return Attachment[]
     */
    public function getInlineAttachmentList(Message $message, Email $email): array
    {
        $inlineAttachmentList = [];

        $this->loadContent($message);

        $bodyPlain = '';
        $bodyHtml = '';

        $htmlPartCount = $this->getMessage($message)->getHtmlPartCount();
        $textPartCount = $this->getMessage($message)->getTextPartCount();

        if (!$htmlPartCount) {
            $bodyHtml = $this->getMessage($message)->getHtmlContent();
        }

        if (!$textPartCount) {
            $bodyPlain = $this->getMessage($message)->getTextContent();
        }

        for ($i = 0; $i < $htmlPartCount; $i++) {
            if ($i) {
                $bodyHtml .= "<br>";
            }

            $inlinePart = $this->getMessage($message)->getHtmlPart($i);

            $bodyHtml .= $inlinePart?->getContent() ?? '';
        }

        for ($i = 0; $i < $textPartCount; $i++) {
            if ($i) {
                $bodyPlain .= "\n";
            }

            $inlinePart = $this->getMessage($message)->getTextPart($i);

            $bodyPlain .= $inlinePart?->getContent() ?? '';
        }

        if ($bodyHtml) {
            $email->setIsHtml();
            $email->setBody($bodyHtml);

            if ($bodyPlain) {
                $email->setBodyPlain($bodyPlain);
            }
        } else {
            $email->setIsHtml(false);
            $email->setBody($bodyPlain);
            $email->setBodyPlain($bodyPlain);
        }

        if (!$email->getBody() && $email->hasBodyPlain()) {
            $email->setBody($email->getBodyPlain());
        }

        $attachmentPartList = $this->getMessage($message)->getAllAttachmentParts();

        $inlineAttachmentMap = [];

        foreach ($attachmentPartList as $i => $attachmentPart) {
            if (!$attachmentPart instanceof MimePart) {
                continue;
            }

            $attachment = $this->entityManager->getRDBRepositoryByClass(Attachment::class)->getNew();

            $filename = $this->extractFileName($attachmentPart, $i);
            $contentType = $this->detectAttachmentContentType($attachmentPart, $filename);
            $disposition = $attachmentPart->getHeaderValue(HeaderConsts::CONTENT_DISPOSITION);

            if ($contentType) {
                $contentType = strtolower($contentType);
            }

            $attachment->setName($filename);
            $attachment->setType($contentType);

            $content = '';

            $binaryContentStream = $attachmentPart->getBinaryContentStream();

            if ($binaryContentStream) {
                $content = $binaryContentStream->getContents();
            }

            $contentId = $attachmentPart->getHeaderValue('Content-ID');

            if ($contentId) {
                $contentId = trim($contentId, '<>');
            }

            if ($disposition === self::DISPOSITION_INLINE) {
                $attachment->setRole(Attachment::ROLE_INLINE_ATTACHMENT);
                $attachment->setTargetField(self::FIELD_BODY);
            } else {
                $attachment->setRole(Attachment::ROLE_ATTACHMENT);
                $attachment->setTargetField(self::FIELD_ATTACHMENTS);
            }

            $attachment->setContents($content);

            $this->entityManager->saveEntity($attachment);

            if ($attachment->getRole() === Attachment::ROLE_ATTACHMENT) {
                $email->addLinkMultipleId(self::FIELD_ATTACHMENTS, $attachment->getId());

                if ($contentId) {
                    $inlineAttachmentMap[$contentId] = $attachment;
                }

                continue;
            }

            // Inline disposition.

            if ($contentId) {
                $inlineAttachmentMap[$contentId] = $attachment;

                $inlineAttachmentList[] = $attachment;

                continue;
            }

            // No ID found, fallback to attachment.
            $attachment
                ->setRole(Attachment::ROLE_ATTACHMENT)
                ->setTargetField(self::FIELD_ATTACHMENTS);

            $this->entityManager->saveEntity($attachment);

            $email->addLinkMultipleId(self::FIELD_ATTACHMENTS, $attachment->getId());
        }

        $body = $email->getBody();

        if ($body) {
            foreach ($inlineAttachmentMap as $cid => $attachment) {
                if (str_contains($body, 'cid:' . $cid)) {
                    $body = str_replace(
                        'cid:' . $cid,
                        '?entryPoint=attachment&amp;id=' . $attachment->getId(),
                        $body
                    );

                    continue;
                }

                // Fallback to attachment.
                if ($attachment->getRole() === Attachment::ROLE_INLINE_ATTACHMENT) {
                    $attachment
                        ->setRole(Attachment::ROLE_ATTACHMENT)
                        ->setTargetField(self::FIELD_ATTACHMENTS);

                    $this->entityManager->saveEntity($attachment);

                    $email->addLinkMultipleId(self::FIELD_ATTACHMENTS, $attachment->getId());
                }
            }

            $email->setBody($body);
        }

        /** @var ?MessagePart $textCalendarPart */
        $textCalendarPart =
            $this->getMessage($message)->getAllPartsByMimeType('text/calendar')[0] ??
            $this->getMessage($message)->getAllPartsByMimeType('application/ics')[0] ??
            null;

        if ($textCalendarPart && $textCalendarPart->hasContent()) {
            $email->set('icsContents', $textCalendarPart->getContent());
        }

        return $inlineAttachmentList;
    }

    private function detectAttachmentContentType(MimePart $part, ?string $filename): ?string
    {
        $contentType = $part->getHeaderValue(HeaderConsts::CONTENT_TYPE);

        if ($contentType && strtolower($contentType) !== self::TYPE_OCTET_STREAM) {
            return $contentType;
        }

        if (!$filename) {
            return null;
        }

        $ext = $this->getAttachmentFilenameExtension($filename);

        if (!$ext) {
            return null;
        }

        return $this->extMimeTypeMap[$ext] ?? null;
    }

    private function getAttachmentFilenameExtension(string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($ext === '') {
            return null;
        }

        return strtolower($ext);
    }

    private function extractFileName(MimePart $attachmentPart, int $i): string
    {
        $filename = $attachmentPart->getHeaderParameter(HeaderConsts::CONTENT_DISPOSITION, 'filename');

        if ($filename === null) {
            $filename = $attachmentPart->getHeaderParameter(HeaderConsts::CONTENT_TYPE, 'name');
        }

        if ($filename === null && $attachmentPart->getContentType() === self::TYPE_MESSAGE_RFC822) {
            $filename = 'message-' . ($i + 1) . '.eml';
        }

        if ($filename === null) {
            $filename = 'unnamed';
        }

        return $filename;
    }
}
