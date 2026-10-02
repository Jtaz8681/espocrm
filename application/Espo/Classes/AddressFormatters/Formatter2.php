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

namespace Espo\Classes\AddressFormatters;

use Espo\Core\Field\Address;
use Espo\Core\Field\Address\AddressFormatter;

class Formatter2 implements AddressFormatter
{
    public function format(Address $address): string
    {
        $result = '';

        $street = $address->getStreet();
        $city = $address->getCity();
        $country = $address->getCountry();
        $state = $address->getState();
        $postalCode = $address->getPostalCode();

        if ($street) {
            $result .= $street;
        }

        if ($city || $postalCode) {
            if ($result) {
                $result .= "\n";
            }

            if ($postalCode) {
                $result .= $postalCode;
            }

            if ($postalCode && $city) {
                $result .= ' ';
            }

            if ($city) {
                $result .= $city;
            }
        }

        if ($state || $country) {
            if ($result) {
                $result .= "\n";
            }

            if ($state) {
                $result .= $state;
            }

            if ($state && $country) {
                $result .= ' ';
            }

            if ($country) {
                $result .= $country;
            }
        }

        return $result;
    }
}
