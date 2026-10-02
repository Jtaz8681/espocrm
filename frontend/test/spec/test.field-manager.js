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

describe('field-manager', () => {
    let fieldManager;

    const defs = {
        'varchar': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                },
                {
                    'name': 'maxLength',
                    'type': 'int'
                }
            ]
        },
        'int': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                },
                {
                    'name': 'min',
                    'type': 'int'
                },
                {
                    'name': 'max',
                    'type': 'int'
                }
            ]
        },
        'float': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                },
                {
                    'name': 'min',
                    'type': 'float'
                },
                {
                    'name': 'max',
                    'type': 'float'
                }
            ]
        },
        'enum': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                },
                {
                    'name': 'options',
                    'type': 'array'
                }
            ]
        },
        'bool': {
            'params': []
        },
        'date': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                }
            ]
        },
        'datetime': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                }
            ]
        },
        'link': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                }
            ],
            'actualFields': ['id']
        },
        'linkParent': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                }
            ],
            'actualFields': ['id', 'type']
        },
        'linkMultiple': {
            'params': [
                {
                    'name': 'required',
                    'type': 'bool',
                    'default': false
                }
            ],
            'actualFields': ['ids']
        },
        'personName': {
            'actualFields': ['salutation', 'first', 'last'],
            'fields': {
                'salutation': {
                    'type': 'varchar'
                },
                'first': {
                    'type': 'enum'
                },
                'last': {
                    'type': 'varchar'
                }
            },
            'naming': 'prefix',
            'notMergeable': true,
        },
        'address': {
            'actualFields': ['street', 'city', 'state', 'country', 'postalCode'],
            'fields': {
                'street': {
                    'type': 'varchar'
                },
                'city': {
                    'type': 'varchar'
                },
                'state': {
                    'type': 'varchar'
                },
                'country': {
                    'type': 'varchar'
                },
                'postalCode': {
                    'type': 'varchar'
                },
            },
            'notMergeable': true
        }
    };

    beforeEach(done => {
		require(['field-manager', 'utils'], FieldManager => {
			fieldManager = new FieldManager(defs);
			done();
		});
	});

	it ('#isMergable should work correctly', () => {
		expect(fieldManager.isMergeable('address')).toBe(false);
		expect(fieldManager.isMergeable('link')).toBe(true);
	});

	it ('#getActualAttributeList should work correctly', () => {
        let fields = fieldManager.getActualAttributeList('address', 'billingAddress');

        expect(fields[0]).toBe('billingAddressStreet');

        fields = fieldManager.getActualAttributeList('personName', 'name');
        expect(fields[2]).toBe('lastName');

        fields = fieldManager.getActualAttributeList('link', 'account');
        expect(fields[0]).toBe('accountId');

        fields = fieldManager.getActualAttributeList('varchar', 'name');
        expect(fields[0]).toBe('name');
	});
});
