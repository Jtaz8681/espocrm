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

describe('acl-manager', () => {
    let acl;

    beforeEach(function (done) {
		require('acl-manager', Acl => {
			acl = new Acl();

			acl.user = {
				isAdmin: () => false
            };
			done();
		});
	});

	it("should check an access properly", () => {
		acl.set({
			table: {
				Lead: {
					read: 'team',
					edit: 'own',
					delete: 'no',
				},
				Opportunity: false,
				Meeting: true
			}
		});

		expect(acl.check('Lead', 'read')).toBe(true);

		expect(acl.check('Lead', 'read', false, false)).toBe(true);
		expect(acl.check('Lead', 'read', false, true)).toBe(true);
		expect(acl.check('Lead', 'read', true)).toBe(true);

		expect(acl.check('Lead', 'edit')).toBe(true);
		expect(acl.check('Lead', 'edit', false, true)).toBe(true);
		expect(acl.check('Lead', 'edit', true, false)).toBe(true);

		expect(acl.check('Lead', 'delete')).toBe(false);

		expect(acl.check('Account', 'edit')).toBe(false);

		expect(acl.check('Opportunity', 'edit')).toBe(false);
		expect(acl.check('Meeting', 'edit')).toBe(true);
	});
});
