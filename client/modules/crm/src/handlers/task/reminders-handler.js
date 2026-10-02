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

import {Events} from 'bullbone';

/**
 * @mixes Bull.Events
 */
class RemindersHandler  {

    /**
     * @param {import('views/record/detail').default} view
     */
    constructor(view) {
        this.view = view;
        this.model = view.model;
        this.user = this.view.getUser();

        this.ignoreStatusList = [
            ...(this.view.getMetadata().get(['scopes', this.view.entityType, 'completedStatusList']) || []),
            ...(this.view.getMetadata().get(['scopes', this.view.entityType, 'canceledStatusList']) || []),
        ];
    }

    process() {
        this.control();

        this.listenTo(this.model, 'change', () => {
            if (
                !this.model.hasChanged('assignedUserId') &&
                !this.model.hasChanged('assignedUsersIds') &&
                !this.model.hasChanged('dateEnd') &&
                !this.model.hasChanged('dateEndDate') &&
                !this.model.hasChanged('status')
            ) {
                return;
            }

            this.control();
        });
    }

    control() {
        if (!this.model.attributes.dateEnd && !this.model.attributes.dateEndDate) {
            this.view.hideField('reminders');

            return;
        }

        /** @type {string[]} */
        const assignedUsersIds = this.model.attributes.assignedUsersIds || [];

        if (
            !this.ignoreStatusList.includes(this.model.attributes.status) &&
            (
                this.model.attributes.assignedUserId === this.user.id ||
                assignedUsersIds.includes(this.user.id)
            )
        ) {
            this.view.showField('reminders');

            return;
        }

        this.view.hideField('reminders');
    }
}

Object.assign(RemindersHandler.prototype, Events);

// noinspection JSUnusedGlobalSymbols
export default RemindersHandler;
