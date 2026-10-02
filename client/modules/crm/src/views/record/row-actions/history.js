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

import RelationshipRowActionsView from 'views/record/row-actions/relationship';

export default class ActivitiesRowActionsView extends RelationshipRowActionsView {

    setup() {
        super.setup();

        this.options.unlinkDisabled = true;
    }

    getActionList() {
        const list = super.getActionList();

        if (this.model.entityType === 'Email' && this.getAcl().checkScope('Email', 'create')) {
            list.push({
                action: 'reply',
                text: this.translate('Reply', 'labels', 'Email'),
                data: {
                    id: this.model.id
                },
                groupIndex: 1,
                iconClass: 'fas fa-reply',
            });
        }

        return list;
    }
}
