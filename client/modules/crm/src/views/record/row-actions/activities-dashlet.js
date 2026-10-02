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

import DefaultRowActionsView from 'views/record/row-actions/default';

export default class ActivitiesDashletRowActionsView extends DefaultRowActionsView {

    getActionList() {
        const actionList =  super.getActionList();

        const entityType = this.model.entityType;

        /** @type {string[]} */
        const options = this.model.getFieldParam('status', 'options') ?? [];

        const notActualStatuses = [
            ...this.getMetadata().get(`scopes.${this.model.entityType}.completedStatusList`, []),
            ...this.getMetadata().get(`scopes.${this.model.entityType}.canceledStatusList`, []),
        ];

        if (entityType === 'Task') {
            if (
                this.options.acl.edit &&
                options.includes('Completed') &&
                !notActualStatuses.includes(this.model.attributes.status)
            ) {
                actionList.push({
                    action: 'setCompleted',
                    label: 'Complete',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 1,
                    iconClass: 'fas fa-check',
                });
            }
        } else if (
            this.options.acl.edit &&
            !notActualStatuses.includes(this.model.attributes.status)
        ) {
            if (options.includes('Held')) {
                actionList.push({
                    action: 'setHeld',
                    label: 'Set Held',
                    data: {
                        id: this.model.id,
                        scope: this.model.entityType
                    },
                    groupIndex: 1,
                    iconClass: 'fas fa-check',
                });
            }

            if (options.includes('Not Held')) {
                actionList.push({
                    action: 'setNotHeld',
                    label: 'Set Not Held',
                    data: {
                        id: this.model.id,
                        scope: this.model.entityType
                    },
                    groupIndex: 1,
                });
            }
        }

        return actionList;
    }
}
