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

describe('model', () => {
	let model;

	beforeEach(done => {
		Espo.loader.require(['model', 'utils'], ModelBase => {
			const Model = class extends ModelBase {};

			model = new Model({}, {
                entityType: 'Some',
                defs: {
                    fields: {
                        'id': {
                            type: 'id',
                        },
                        'name': {
                            maxLength: 150,
                            required: true,
                        },
                        'email': {
                            type: 'email',
                        },
                        'phone': {
                            type: 'phone',
                            maxLength: 50,
                            default: '007',
                        },
                    },
                },
            });

			done();
		});
	});

	it('should set urlRoot as name', () => {
		expect(model.urlRoot).toBe('Some');
	});

	it('#getFieldType should return field type or null if undefined', () => {
		expect(model.getFieldType('email')).toBe('email');
		expect(model.getFieldType('name')).toBe(null);
	});

	it('#isRequired should return true if field is required and false if not', () => {
		expect(model.isRequired('name')).toBe(true);
		expect(model.isRequired('email')).toBe(false);
	});

	it('should set defaults correctly', () => {
		model.populateDefaults();
		expect(model.get('phone')).toBe('007');
	});

    it('should fire change event on set 1', () => {
        return new Promise(resolve => {
            model.once('change', () => {
                expect(model.get('name')).toBe('test');

                resolve();
            });

            model.set('name', 'test');
        });
    });

    it('should fire change event on set 2', () => {
        return new Promise(resolve => {
            model.once('change:name', (m, v) => {
                expect(model.get('name')).toBe('test');
                expect(v).toBe('test');

                resolve();
            });

            model.set({'name': 'test'});
        });
    });

    it('should not fire change event on set with silent option', () => {
        return new Promise((resolve, reject) => {
            model.once('change:name', () => {
                reject();
            });

            model.once('change:hello', (m, v, o) => {
                expect(o.test).toBe(true);

                resolve();
            });

            model.setMultiple({'name': 'test'}, {silent: true});
            model.setMultiple({'hello': 'hello'}, {test: true});
        });
    });

    it('should unset attribute', () => {
        model.set('name', 'test');

        expect(model.has('name')).toBeTrue();

        model.unset('name');

        expect(model.has('name')).toBeFalse();
    });

    it('should unset attribute and fire events', () => {
        return new Promise((resolve, reject) => {
            model.setMultiple({
                'name': '1',
                'hello': 1,
            });

            model.once('change:name', () => {
                reject();
            });

            model.once('change:hello', (m, v, o) => {
                expect(o.test).toBe(true);

                resolve();
            });

            model.unset('name', {silent: true});
            model.unset('hello', {test: true});
        });
    });

    it('should clear attributes', () => {
        model.set('name', 'test');

        expect(model.has('name')).toBeTrue();

        model.clear();

        expect(model.has('name')).toBeFalse();
    });

    it('should update with PUT', () => {
        model.id = '000';
        model.set('test1', '1');
        model.set('test2', '2');

        return new Promise(resolve => {
            model.on('request', (url, method, data) => {
                expect(url).toBe('Some/000');
                expect(method).toBe('PUT');
                expect(data).toEqual({
                    test1: '1',
                    test2: '2',
                    test3: '3',
                });

                resolve();
            });

            model.save({test3: '3'}, {bypassRequest: true});
        });
    });

    it('should patch with PUT', () => {
        model.id = '000';
        model.set('test1', '1');
        model.set('test2', '2');

        return new Promise(resolve => {
            model.on('request', (url, method, data) => {
                expect(url).toBe('Some/000');
                expect(method).toBe('PUT');
                expect(data).toEqual({
                    test3: '3',
                });

                resolve();
            });

            model.save({test3: '3'}, {bypassRequest: true, patch: true});
        });
    });

    it('should create with POST', () => {
        model.set('test1', '1');
        model.set('test2', '2');

        return new Promise(resolve => {
            model.on('request', (url, method, data) => {
                expect(url).toBe('Some');
                expect(method).toBe('POST');
                expect(data).toEqual({
                    test1: '1',
                    test2: '2',
                    test3: '3',
                });

                resolve();
            });

            model.save({test3: '3'}, {bypassRequest: true});
        });
    });

    it('should destroy with DELETE', () => {
        model.id = '000';
        model.set('test1', '1');
        model.set('test2', '2');

        return new Promise(resolve => {
            model.on('request', (url, method) => {
                expect(url).toBe('Some/000');
                expect(method).toBe('DELETE');

                resolve();
            });

            model.destroy({bypassRequest: true});
        });
    });
});
