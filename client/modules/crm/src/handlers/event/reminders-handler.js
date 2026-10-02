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
        /** @type {import('model').default} */
        this.model = view.model;
        /** @type {import('models/user').default} */
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
                !this.model.hasChanged('usersIds') &&
                !this.model.hasChanged('assignedUsersIds') &&
                !this.model.hasChanged('status')
            ) {
                return;
            }

            this.control();
        });
    }

    control() {
        const usersIds = /** @type {string[]} */this.model.get('usersIds') || [];
        const assignedUsersIds = /** @type {string[]} */this.model.get('assignedUsersIds') || [];

        if (
            !this.ignoreStatusList.includes(this.model.get('status')) &&
            (
                this.model.get('assignedUserId') === this.user.id ||
                usersIds.includes(this.user.id) ||
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
