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

namespace Espo\Core\Mail\Event;

use Espo\Core\Utils\DateTime as DateTimeUtil;

use DateTime;
use DateTimeZone;
use RuntimeException;

class Event
{
    private ?string $attendees = null;
    private ?string $organizer = null;
    private ?string $dateStart = null;
    private ?string $dateEnd = null;
    private ?string $location = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?string $timezone = null;
    private ?string $uid = null;

    private bool $isAllDay = false;

    public function withAttendees(?string $attendees): self
    {
        $obj = clone $this;
        $obj->attendees = $attendees;

        return $obj;
    }

    public function withOrganizer(?string $organizer): self
    {
        $obj = clone $this;
        $obj->organizer = $organizer;

        return $obj;
    }

    public function withDateStart(?string $dateStart): self
    {
        $obj = clone $this;
        $obj->dateStart = $dateStart;

        return $obj;
    }

    public function withDateEnd(?string $dateEnd): self
    {
        $obj = clone $this;
        $obj->dateEnd = $dateEnd;

        return $obj;
    }

    public function withLocation(?string $location): self
    {
        $obj = clone $this;
        $obj->location = $location;

        return $obj;
    }

    public function withName(?string $name): self
    {
        $obj = clone $this;
        $obj->name = $name;

        return $obj;
    }

    public function withDescription(?string $description): self
    {
        $obj = clone $this;
        $obj->description = $description;

        return $obj;
    }

    public function withTimezone(?string $timezone): self
    {
        $obj = clone $this;
        $obj->timezone = $timezone;

        return $obj;
    }

    public function withUid(?string $uid): self
    {
        $obj = clone $this;
        $obj->uid = $uid;

        return $obj;
    }

    public function withIsAllDay(bool $isAllDay): self
    {
        $obj = clone $this;
        $obj->isAllDay = $isAllDay;

        return $obj;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function isAllDay(): bool
    {
        return $this->isAllDay;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDateStart(): ?string
    {
        return $this->convertDate($this->dateStart);
    }

    public function getDateEnd(): ?string
    {
        return $this->convertDate($this->dateEnd, true);
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public static function create(): self
    {
        return new self();
    }

    private function convertDate(?string $value, bool $isEnd = false): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($this->isAllDay) {
            $dt = DateTime::createFromFormat('Ymd', $value);

            if ($dt === false) {
                throw new RuntimeException("Could not parse '{$value}'.");
            }

            if ($isEnd) {
                $dt->modify('-1 day');
            }

            return $dt->format(DateTimeUtil::SYSTEM_DATE_FORMAT);
        }

        $timezone = $this->timezone ?? 'UTC';

        $dt = DateTime::createFromFormat('Ymd\THis', $value, new DateTimeZone($timezone));

        if ($dt === false) {
            throw new RuntimeException("Could not parse '{$value}'.");
        }

        $dt->setTimezone(new DateTimeZone('UTC'));

        return $dt->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
    }

    public function getOrganizerEmailAddress(): ?string
    {
        return $this->getEmailAddressFromAttendee($this->organizer);
    }

    /**
     * @return string[]
     */
    public function getAttendeeEmailAddressList(): array
    {
        if ($this->attendees === null || $this->attendees === '') {
            return [];
        }

        $list = [];

        foreach (explode(',', $this->attendees) as $item) {
            $emailAddress = $this->getEmailAddressFromAttendee($item);

            if ($emailAddress === null) {
                continue;
            }

            $list[] = $emailAddress;
        }

        return $list;
    }

    private function getEmailAddressFromAttendee(?string $item): ?string
    {
        if ($item === null || $item === '') {
            return null;
        }

        if (explode(':', $item)[0] !== 'MAILTO') {
            return null;
        }

        return explode(':', $item)[1] ?? null;
    }
}
