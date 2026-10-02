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
use Espo\Entities\EmailFilter;

class FiltersMatcher
{
    /**
     * @param iterable<EmailFilter> $filterList
     * @param bool $skipBody Not to match if the body-contains is not empty.
     */
    public function findMatch(Email $email, $filterList, bool $skipBody = false): ?EmailFilter
    {
        foreach ($filterList as $filter) {
            if ($this->match($email, $filter, $skipBody)) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * @param bool $skipBody Not to match if the body-contains is not empty.
     */
    public function match(Email $email, EmailFilter $filter, bool $skipBody = false): bool
    {
        $filterCount = 0;

        $from = $filter->getFrom();
        $subject = $filter->getSubject();

        if ($from) {
            $filterCount++;

            if (
                !$this->matchString(
                    strtolower($from),
                    strtolower($email->getFromAddress() ?? '')
                )
            ) {
                return false;
            }
        }

        if ($filter->getTo()) {
            $filterCount++;

            if (!$this->matchTo($email, $filter)) {
                return false;
            }
        }

        if ($subject) {
            $filterCount++;

            if (
                !$this->matchString($subject, $email->getSubject() ?? '')
            ) {
                return false;
            }
        }

        if (count($filter->getBodyContains())) {
            $filterCount++;

            if ($skipBody) {
                return false;
            }

            if (!$this->matchBody($email, $filter)) {
                return false;
            }
        }

        if (count($filter->getBodyContainsAll())) {
            $filterCount++;

            if ($skipBody) {
                return false;
            }

            if (!$this->matchBodyAll($email, $filter)) {
                return false;
            }
        }

        if ($filterCount) {
            return true;
        }

        return false;
    }

    private function matchTo(Email $email, EmailFilter $filter): bool
    {
        $filterTo = $filter->getTo();

        if ($filterTo === null) {
            return false;
        }

        if (count($email->getToAddressList())) {
            foreach ($email->getToAddressList() as $to) {
                if (
                    $this->matchString(
                        strtolower($filterTo),
                        strtolower($to)
                    )
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchBody(Email $email, EmailFilter $filter): bool
    {
        $phraseList = $filter->getBodyContains();
        $body = $email->getBody();
        $bodyPlain = $email->getBodyPlain();

        foreach ($phraseList as $phrase) {
            if ($phrase === '') {
                continue;
            }

            if ($bodyPlain && stripos($bodyPlain, $phrase) !== false) {
                return true;
            }

            if ($body && stripos($body, $phrase) !== false) {
                return true;
            }
        }

        return false;
    }

    private function matchBodyAll(Email $email, EmailFilter $filter): bool
    {
        $phraseList = $filter->getBodyContainsAll();
        $body = $email->getBody() ?? $email->getBodyPlain() ?? '';

        if ($phraseList === []) {
            return true;
        }

        foreach ($phraseList as $phrase) {
            if ($phrase === '') {
                continue;
            }

            if (stripos($body, $phrase) === false) {
                return false;
            }
        }

        return true;
    }

    private function matchString(string $pattern, string $value): bool
    {
        if ($pattern == $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern) . '\z';

        if (preg_match('#^' . $pattern . '#', $value)) {
            return true;
        }

        return false;
    }
}
