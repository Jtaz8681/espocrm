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
        const actionList = super.getActionList();

        if (this.options.acl.edit) {
            if (this.model.entityType === 'Meeting' || this.model.entityType === 'Call') {
                /** @type {string[]} */
                const options = this.model.getFieldParam('status', 'options') ?? [];

                const notActualStatuses = [
                    ...this.getMetadata().get(`scopes.${this.model.entityType}.completedStatusList`, []),
                    ...this.getMetadata().get(`scopes.${this.model.entityType}.canceledStatusList`, []),
                ];

                if (options.includes('Held') && !notActualStatuses.includes(this.model.attributes.status)) {
                    actionList.push({
                        action: 'setHeld',
                        text: this.translate('Set Held', 'labels', 'Meeting'),
                        data: {
                            id: this.model.id,
                        },
                        groupIndex: 1,
                        iconClass: 'fas fa-check',
                    });
                }

                if (options.includes('Not Held') && !notActualStatuses.includes(this.model.attributes.status)) {
                    actionList.push({
                        action: 'setNotHeld',
                        text: this.translate('Set Not Held', 'labels', 'Meeting'),
                        data: {
                            id: this.model.id,
                        },
                        groupIndex: 1,
                    });
                }
            }
        }

        return actionList;
    }
}
