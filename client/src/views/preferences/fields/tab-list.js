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

import TabListFieldView from 'views/settings/fields/tab-list';

export default class extends TabListFieldView {

    setup() {
        super.setup();

        this.params.options = this.params.options.filter(scope => {
            if (scope === '_delimiter_' || scope === 'Home') {
                return true;
            }

            const defs = this.getMetadata().get(['scopes', scope]);

            if (!defs) {
                return;
            }

            if (defs.disabled) {
                return;
            }

            if (defs.acl) {
                return this.getAcl().check(scope);
            }

            if (defs.tabAclPermission) {
                const level = this.getAcl().getPermissionLevel(defs.tabAclPermission);

                return level && level !== 'no';
            }

            return true;
        });
    }
}
