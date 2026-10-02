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
import EntityTypeListFieldView from 'views/fields/entity-type-list';

// noinspection JSUnusedGlobalSymbols
export default class extends EntityTypeListFieldView {

    setupOptions() {
        super.setupOptions();

        this.params.options = this.params.options.filter(scope => {
            if (this.getMetadata().get(['scopes', scope, 'disabled'])) {
                return;
            }

            if (!this.getMetadata().get(['scopes', scope, 'object'])) {
                return;
            }

            if (!this.getMetadata().get(['scopes', scope, 'calendar'])) {
                return;
            }

            const fieldType = this.getFieldManager().getEntityTypeFieldParam(scope, 'dateStart', 'type');

            if (fieldType === 'date') {
                return false;
            }

            return true;
        });
    }
}
