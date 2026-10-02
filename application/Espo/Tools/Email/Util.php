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

namespace Espo\Tools\Email;

use League\HTMLToMarkdown\HtmlConverter;

class Util
{
    static public function parseFromName(string $string): string
    {
        $fromName = '';

        if ($string && stripos($string, '<') !== false) {
            /** @var string $replacedString */
            $replacedString = preg_replace('/(<.*>)/', '', $string);

            $fromName = trim($replacedString, '" ');
        }

        return $fromName;
    }

    static public function parseFromAddress(string $string): string
    {
        if (!$string) {
            return '';
        }

        if (stripos($string, '<') !== false) {
            $fromAddress = '';

            if (preg_match('/<(.*)>/', $string, $matches)) {
                $fromAddress = trim($matches[1]);
            }

            return $fromAddress;
        }

        return $string;
    }

    /**
     * Strip HTML.
     *
     * @since 9.1.0
     */
    static public function stripHtml(string $string): string
    {
        if (!$string) {
            return '';
        }

        $converter = new HtmlConverter();
        $converter->setOptions([
            'remove_nodes' => 'img',
            'strip_tags' => true,
        ]);

        $string = $converter->convert($string) ?: '';

        $string = (string) preg_replace('~\R~u', "\r\n", $string);

        $reList = [
            '&(quot|#34);',
            '&(amp|#38);',
            '&(lt|#60);',
            '&(gt|#62);',
            '&(nbsp|#160);',
            '&(iexcl|#161);',
            '&(cent|#162);',
            '&(pound|#163);',
            '&(copy|#169);',
            '&(reg|#174);',
        ];

        $replaceList = [
            '',
            '&',
            '<',
            '>',
            ' ',
            '¡',
            '¢',
            '£',
            '©',
            '®',
        ];

        foreach ($reList as $i => $re) {
            $string = (string) mb_ereg_replace($re, $replaceList[$i], $string, 'i');
        }



        return $string;
    }

    /**
     * Strip a quote part in a plain text.
     *
     * @since 9.0.0
     */
    static public function stripPlainTextQuotePart(string $string): string
    {
        if (!$string) {
            return '';
        }

        $lines = preg_split("/\r\n|\n|\r/", $string);

        if (!is_array($lines)) {
            return '';
        }

        $endIndex = count($lines) - 1;

        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = $lines[$i];

            if (str_starts_with($line, '>') || $line === '') {
                $endIndex = $i;

                continue;
            }

            break;
        }

        $lines = array_slice($lines, 0, $endIndex);

        if (count($lines) > 2) {
            $lastIndex = count($lines) - 1;

            if (str_ends_with($lines[$lastIndex], ':') && $lines[$lastIndex - 1] === '') {
                $lines = array_slice($lines, 0, count($lines) - 2);
            }
        }

        return implode("\r\n", $lines);
    }
}
