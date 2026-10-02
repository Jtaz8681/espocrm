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

import DetailBottomRecordView from 'views/record/detail-bottom';

class UserDetailBottomRecordView extends  DetailBottomRecordView {

    setupPanels() {
       super.setupPanels();

       const userModel = /** @type {import('models/user').default} */ this.model;

        const streamAllowed = this.getAcl().checkPermission('user', userModel);

        if (
            !streamAllowed &&
            this.getAcl().getPermissionLevel('user') === 'team' &&
            !this.model.has('teamsIds')
        ) {
            this.listenToOnce(this.model, 'sync', () => {
                if (this.getAcl().checkPermission('user', userModel)) {
                    this.onPanelsReady(() => {
                        this.showPanel('stream', 'acl');
                    });
                }
            });
        }

        this.panelList.push({
            "name": "stream",
            "label": "Stream",
            "view": "views/user/record/panels/stream",
            "sticked": false,
            "hidden": !streamAllowed,
        });

        if (!streamAllowed) {
            this.recordHelper.setPanelStateParam('stream', 'hiddenAclLocked', true);
        }
    }
}

export default UserDetailBottomRecordView;
