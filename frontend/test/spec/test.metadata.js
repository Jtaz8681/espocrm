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

describe('metadata', () => {
    let metadata;

    beforeEach(done => {
		require('metadata', Metadata => {
			metadata = new Metadata();
			metadata.data = {
				recordDefs: {
					Lead: {
						some: {type: 'varchar'},
					}
				}
			};
			done();
		});
	});

	it('#get should work correctly', () => {
		expect(metadata.get('recordDefs.Lead.some')).toBe(metadata.data.recordDefs.Lead.some);
		expect(metadata.get('recordDefs.Contact')).toBe(null);
	});
});
