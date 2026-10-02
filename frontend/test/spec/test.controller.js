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

describe('controller', () => {
    let controller;
    let viewFactory;
    let view;

    let ControllerClass;

    beforeEach(done => {
		Espo.loader.require('controller', Controller => {
			ControllerClass = Controller;

			viewFactory = {
				create: {}
			};

			view = {
				render: {},
				setView: {}
			};

			controller = new Controller({}, {viewFactory: viewFactory});
			spyOn(viewFactory, 'create').and.returnValue(view);
			spyOn(view, 'render');
			spyOn(view, 'setView');

			done();
		});
	});

	it ('#set should set param', () => {
		controller.set('some', 'test');
		expect(controller.params['some']).toBe('test');
	});

	it ('#get should get param', () => {
		controller.set('some', 'test');
		expect(controller.get('some')).toBe('test');
	});

	it ("different controllers should use same param set", () => {
        const someController = new ControllerClass(controller.params, {viewFactory: viewFactory});

        someController.set('some', 'test');
		expect(controller.get('some')).toBe(someController.get('some'));
	});
});
