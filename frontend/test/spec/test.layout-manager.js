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

describe('layout-manager', () => {
    let layoutManager;

    beforeEach((done) => {
		require('layout-manager', (LayoutManager) => {
			layoutManager = new LayoutManager();
			spyOn(layoutManager.ajax, 'getRequest')
                .and
                .callFake((options) => Promise.resolve());

			done();
		});
	});

	it("should call ajax to fetch new layout", () => {
		layoutManager.get('some', 'list');

		expect(layoutManager.ajax.getRequest.calls.mostRecent().args[0]).toBe('some/layout/list');
	});
});
