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

import DetailSideRecordView from 'views/record/detail-side';

export default class UserDetailSideRecordView extends DetailSideRecordView {

    setupPanels() {
        super.setupPanels();

        const userModel = /** @type {import('modules/user').default} */this.model;

        if (userModel.isApi() || userModel.isSystem()) {
            this.hidePanel('activities', true);
            this.hidePanel('history', true);
            this.hidePanel('tasks', true);
            this.hidePanel('stream', true);

            return;
        }

        const showActivities = this.getAcl().checkPermission('userCalendar', userModel);

        if (
            !showActivities &&
            this.getAcl().getPermissionLevel('userCalendar') === 'team' &&
            !this.model.has('teamsIds')
        ) {
            this.listenToOnce(this.model, 'sync', () => {
                if (!this.getAcl().checkPermission('userCalendar', userModel)) {
                    return;
                }

                this.onPanelsReady(() => {
                    this.showPanel('activities', 'acl');
                    this.showPanel('history', 'acl');

                    if (!userModel.isPortal()) {
                        this.showPanel('tasks', 'acl');
                    }
                });
            });
        }

        if (!showActivities) {
            this.hidePanel('activities', false, 'acl');
            this.hidePanel('history', false, 'acl');
            this.hidePanel('tasks', false, 'acl');
        }

        if (userModel) {
            this.hidePanel('tasks', true);
        }
    }
}
