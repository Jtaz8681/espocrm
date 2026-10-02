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

import BaseDashletOptionsModalView from 'views/dashlets/options/base';

export default class RecordListDashletOptionsModalView extends BaseDashletOptionsModalView {

    /**
     * @protected
     * @type {boolean}
     */
    hasCollaborators

    setup() {
        const entityType = this.getMetadata().get(`dashlets.${this.name}.entityType`);

        this.hasCollaborators = entityType && !!this.getMetadata().get(`scopes.${entityType}.collaborators`);

        super.setup();

        if (!this.hasCollaborators) {
            this.getRecordView().hideField('includeShared');
        }
    }
}
