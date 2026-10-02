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

import MassUpdateModalView from 'views/modals/mass-update';

export default class extends MassUpdateModalView {

    setup() {
        if (this.options.scope === 'ApiUser') {
            this.layoutName = 'massUpdateApi';
        } else if (this.options.scope === 'PortalUser') {
            this.layoutName = 'massUpdatePortal';
        }

        super.setup();
    }
}
