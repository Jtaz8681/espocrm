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

return [
    'entities' => [
        'User' => [
            [
                'id' => '1',
                'type' => 'admin',
                'userName' => 'admin',
                'password' => '1',
                'salutationName' => '',
                'firstName' => '',
                'lastName' => 'Admin',
                'title' => '',
                'emailAddress' => 'demo@espocrm.com',
                'phoneNumberData' => [
                    (object) [
                        'phoneNumber' => '111',
                        'primary' => true,
                        'type' => 'Office',
                    ],
                ],
            ],
        ],
        'Account' => [
            [
                'id' => '53203b942850b',
                'name' => 'Besharp',
                'website' => 'http://www.be.sharp.ca',
                'phoneNumberData' => [
                    (object) [
                        'phoneNumber' => '311-2233-11',
                        'primary' => true,
                        'type' => 'Office',
                    ],
                    (object) [
                        'phoneNumber' => '311-2233-12',
                        'type' => 'Fax',
                    ],
                ],
                'type' => 'Customer',
                'industry' => 'Apparel',
                'sicCode' => '',
                'billingAddressStreet' => '130 Somerset Street West',
                'billingAddressCity' => 'Ottawa',
                'billingAddressState' => 'Ontario',
                'billingAddressCountry' => 'Canada',
                'billingAddressPostalCode' => 'K3R 0F7',
                'shippingAddressStreet' => '130 Somerset Street West',
                'shippingAddressCity' => 'Ottawa',
                'shippingAddressState' => 'Ontario',
                'shippingAddressCountry' => 'Canada',
                'shippingAddressPostalCode' => 'K3R 0F7',
                'description' => '',
                'emailAddress' => 'supp@be.sharp-example.ca',
                'assignedUserId' => '1',
            ],
            [
                'id' => '53203b9428546',
                'name' => 'Mein Heimathaus',
                'website' => 'http://www.meinheimathaus.de',
                'phoneNumberData' => [
                    (object) [
                        'phoneNumber' => '165-681-158',
                        'primary' => true,
                        'type' => 'Office',
                    ],
                    (object) [
                        'phoneNumber' => '165-681-159',
                        'type' => 'Other',
                    ],
                ],
                'type' => 'Partner',
                'industry' => 'Finance',
                'sicCode' => '',
                'billingAddressStreet' => 'Goethestraße 23',
                'billingAddressCity' => 'Berlin',
                'billingAddressState' => 'Berlin',
                'billingAddressCountry' => 'Germany',
                'billingAddressPostalCode' => '10623',
                'shippingAddressStreet' => 'Goethestraße 23',
                'shippingAddressCity' => 'Berlin',
                'shippingAddressState' => 'Berlin',
                'shippingAddressCountry' => 'Germany',
                'shippingAddressPostalCode' => '10623',
                'description' => '',
                'emailAddress' => 'supp@be1.sharp-example.ca',
                'assignedUserId' => '1',
            ],
        ],
    ],
];
