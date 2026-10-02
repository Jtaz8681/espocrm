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

import RelationshipPanelView from 'views/record/panels/relationship';

export default class extends RelationshipPanelView {

    setupListLayout() {
        const jobWithTargetList = this.getMetadata().get(['clientDefs', 'ScheduledJob', 'jobWithTargetList']) || [];

        if (~jobWithTargetList.indexOf(this.model.get('job'))) {
            this.listLayoutName = 'listSmallWithTarget'
        }
    }
}
