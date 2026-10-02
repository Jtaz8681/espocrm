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

namespace Espo\Modules\Crm\Business\Event;

use RuntimeException;

class Ics
{
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_TENTATIVE = 'TENTATIVE';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const METHOD_REQUEST = 'REQUEST';
    public const METHOD_CANCEL = 'CANCEL';

    /** @var self::METHOD_* string  */
    private string $method;
    private ?string $output = null;
    private string $prodid;
    private ?int $startDate = null;
    private ?int $endDate = null;
    private ?string $summary = null;
    private ?string $address = null;
    private ?string $description = null;
    private ?string $uid = null;
    /** @var self::STATUS_* string */
    private string $status;
    private ?int $stamp = null;
    /** @var array{string, ?string}|null  */
    private ?array $organizer = null;
    /** @var array{string, ?string}[]  */
    private array $attendees = [];

    /**
     * @param array{
     *     organizer?: array{0: string, 1: ?string}|null,
     *     attendees?: array{0: string, 1: ?string, 2: string|null}[],
     *     startDate?: ?int,
     *     endDate?: ?int,
     *     summary?: ?string,
     *     address?: ?string,
     *     description?: ?string,
     *     uid?: ?string,
     *     status?: self::STATUS_CONFIRMED|self::STATUS_TENTATIVE|self::STATUS_CANCELLED,
     *     method?: self::METHOD_REQUEST|self::METHOD_CANCEL,
     *     stamp?: ?int,
     * } $attributes
     */
    public function __construct(string $prodid, array $attributes = [])
    {
        if ($prodid === '') {
            throw new RuntimeException('PRODID is required');
        }

        $this->status = self::STATUS_CONFIRMED;
        $this->method = self::METHOD_REQUEST;
        $this->prodid = $prodid;

        foreach ($attributes as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new RuntimeException("Bad attribute '$key'.");
            }

            $this->$key = $value;
        }
    }

    public function get(): string
    {
        if ($this->output === null) {
            $this->generate();
        }

        /** @var string */
        return $this->output;
    }

    /** @noinspection SpellCheckingInspection */
    private function generate(): void
    {
        $start =
            "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-$this->prodid\r\n" .
            "METHOD:$this->method\r\n" .
            "BEGIN:VEVENT\r\n";

        $organizerPart = '';

        if ($this->organizer) {
            $organizerPart = "ORGANIZER;{$this->preparePerson($this->organizer[0], $this->organizer[1], null)}";
        }

        $locationValuePart = $this->escapeString($this->formatMultiline($this->address));

        $locationPart = $locationValuePart ?
            "LOCATION:$locationValuePart\r\n" : '';

        $body =
            "DTSTART:{$this->formatTimestamp($this->startDate)}\r\n" .
            "DTEND:{$this->formatTimestamp($this->endDate)}\r\n" .
            "SUMMARY:{$this->escapeString($this->formatMultiline($this->summary))}\r\n" .
            $locationPart .
            $organizerPart .
            "DESCRIPTION:{$this->escapeString($this->formatMultiline($this->description))}\r\n" .
            "UID:$this->uid\r\n" .
            "SEQUENCE:0\r\n" .
            "DTSTAMP:{$this->formatTimestamp($this->stamp ?? time())}\r\n" .
            "STATUS:$this->status\r\n";

        foreach ($this->attendees as $attendee) {
            $body .= "ATTENDEE;{$this->preparePerson($attendee[0], $attendee[1], $attendee[2] ?? null)}";
        }

        $end =
            "END:VEVENT\r\n".
            "END:VCALENDAR";

        $this->output = $start . $body . $end;
    }

    private function preparePerson(string $address, ?string $name, ?string $status): string
    {
        $output = '';

        if ($status) {
            $output .= "PARTSTAT=$status;";
        }

        $output .= "CN={$this->escapeString($name)}:MAILTO:{$this->escapeString($address)}\r\n";

        return $output;
    }

    private function formatTimestamp(?int $timestamp): string
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        return date('Ymd\THis\Z', $timestamp);
    }


    private function escapeString(?string $string): string
    {
        if (!$string) {
            return '';
        }

        /** @var string */
        return preg_replace('/([,;])/', '\\\$1', $string);
    }

    private function formatMultiline(?string $string): string
    {
        if (!$string) {
            return '';
        }

        return str_replace(["\r\n", "\n"], "\\n", $string);
    }
}
