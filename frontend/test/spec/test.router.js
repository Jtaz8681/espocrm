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

describe('router', () => {
    let router;

    beforeEach(done => {
		require('router', Router => {
			router = new Router();
			done();
		});
	});

	it('should parse slashed options', () => {
        let options = router._parseOptionsParams("p=1&t=2");
        expect(options.p).toBe('1');
		expect(options.t).toBe('2');

        options = router._parseOptionsParams("p=1&");
        expect(options.p).toBe('1');

        options = router._parseOptionsParams("p");
        expect(options).toBe("p");

        options = router._parseOptionsParams("p=1&t");
        expect(options.p).toBe('1');
		expect(options.t).toBe(true);

        options = router._parseOptionsParams("p=1&t=");
        expect(options.t).toBe('');
	});
});
